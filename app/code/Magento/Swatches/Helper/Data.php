<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Helper;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Swatches\Model\Swatch;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Exception\InputException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Class Helper Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data
{
    /**
     * When we init media gallery empty image types contain this value.
     */
    const EMPTY_IMAGE_VALUE = 'no_selection';

    /**
     * Default store ID
     */
    const DEFAULT_STORE_ID = 0;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SwatchCollectionFactory
     */
    protected $swatchCollectionFactory;

    /**
     * Catalog Image Helper
     *
     * @var Image
     */
    protected $imageHelper;

    /**
     * Product metadata pool
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * Data key which should populated to Attribute entity from "additional_data" field
     *
     * @var array
     */
    protected $eavAttributeAdditionalDataKeys = [
        Swatch::SWATCH_INPUT_TYPE_KEY,
        'update_product_preview_image',
        'use_product_image_for_swatch'
    ];

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param Image $imageHelper
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        SwatchCollectionFactory $swatchCollectionFactory,
        Image $imageHelper
    ) {
        $this->productCollectionFactory   = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function assembleAdditionalDataEavAttribute(Attribute $attribute)
    {
        $initialAdditionalData = [];
        $additionalData = (string) $attribute->getData('additional_data');
        if (!empty($additionalData)) {
            $additionalData = unserialize($additionalData);
            if (is_array($additionalData)) {
                $initialAdditionalData = $additionalData;
            }
        }

        $dataToAdd = [];
        foreach ($this->eavAttributeAdditionalDataKeys as $key) {
            $dataValue = $attribute->getData($key);
            if (null !== $dataValue) {
                $dataToAdd[$key] = $dataValue;
            }
        }
        $additionalData = array_merge($initialAdditionalData, $dataToAdd);
        $attribute->setData('additional_data', serialize($additionalData));
        return $this;
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    private function populateAdditionalDataEavAttribute(Attribute $attribute)
    {
        $additionalData = unserialize($attribute->getData('additional_data'));
        if (isset($additionalData) && is_array($additionalData)) {
            foreach ($this->eavAttributeAdditionalDataKeys as $key) {
                if (isset($additionalData[$key])) {
                    $attribute->setData($key, $additionalData[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * @param string $attributeCode swatch_image|image
     * @param ModelProduct $configurableProduct
     * @param array $requiredAttributes
     * @return bool|Product
     */
    private function loadFirstVariation($attributeCode, ModelProduct $configurableProduct, array $requiredAttributes)
    {
        if ($this->isProductHasSwatch($configurableProduct)) {
            $usedProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);

            foreach ($usedProducts as $simpleProduct) {
                if (!in_array($simpleProduct->getData($attributeCode), [null, self::EMPTY_IMAGE_VALUE], true)
                    && !array_diff_assoc($requiredAttributes, $simpleProduct->getData())
                ) {
                    return $simpleProduct;
                }
            }
        }

        return false;
    }

    /**
     * @param Product $configurableProduct
     * @param array $requiredAttributes
     * @return bool|Product
     */
    public function loadFirstVariationWithSwatchImage(Product $configurableProduct, array $requiredAttributes)
    {
        return $this->loadFirstVariation('swatch_image', $configurableProduct, $requiredAttributes);
    }

    /**
     * @param Product $configurableProduct
     * @param array $requiredAttributes
     * @return bool|Product
     */
    public function loadFirstVariationWithImage(Product $configurableProduct, array $requiredAttributes)
    {
        return $this->loadFirstVariation('image', $configurableProduct, $requiredAttributes);
    }

    /**
     * Load Variation Product using fallback
     *
     * @param Product $parentProduct
     * @param array $attributes
     * @return bool|Product
     */
    public function loadVariationByFallback(Product $parentProduct, array $attributes)
    {
        if (! $this->isProductHasSwatch($parentProduct)) {
            return false;
        }

        $productCollection = $this->productCollectionFactory->create();

        $productLinkedFiled = $this->getMetadataPool()
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
        $parentId = $parentProduct->getData($productLinkedFiled);

        $this->addFilterByParent($productCollection, $parentId);

        $configurableAttributes = $this->getAttributesFromConfigurable($parentProduct);
        $allAttributesArray = [];
        foreach ($configurableAttributes as $attribute) {
            $allAttributesArray[$attribute['attribute_code']] = $attribute['default_value'];
        }

        $resultAttributesToFilter = array_merge(
            $attributes,
            array_diff_key($allAttributesArray, $attributes)
        );

        $this->addFilterByAttributes($productCollection, $resultAttributesToFilter);

        $variationProduct = $productCollection->getFirstItem();
        if ($variationProduct && $variationProduct->getId()) {
            return $this->productRepository->getById($variationProduct->getId());
        }

        return false;
    }

    /**
     * @param ProductCollection $productCollection
     * @param array $attributes
     * @return void
     */
    private function addFilterByAttributes(ProductCollection $productCollection, array $attributes)
    {
        foreach ($attributes as $code => $option) {
            $productCollection->addAttributeToFilter($code, ['eq' => $option]);
        }
    }

    /**
     * @param ProductCollection $productCollection
     * @param integer $parentId
     * @return void
     */
    private function addFilterByParent(ProductCollection $productCollection, $parentId)
    {
        $tableProductRelation = $productCollection->getTable('catalog_product_relation');
        $productCollection
            ->getSelect()
            ->join(
                ['pr' => $tableProductRelation],
                'e.entity_id = pr.child_id'
            )
            ->where('pr.parent_id = ?', $parentId);
    }

    /**
     * Method getting full media gallery for current Product
     * Array structure: [
     *  ['image'] => 'http://url/pub/media/catalog/product/2/0/blabla.jpg',
     *  ['mediaGallery'] => [
     *      galleryImageId1 => simpleProductImage1.jpg,
     *      galleryImageId2 => simpleProductImage2.jpg,
     *      ...,
     *      ]
     * ]
     * @param ModelProduct $product
     * @return array
     */
    public function getProductMediaGallery(ModelProduct $product)
    {
        if (!in_array($product->getData('image'), [null, self::EMPTY_IMAGE_VALUE], true)) {
            $baseImage = $product->getData('image');
        } else {
            $productMediaAttributes = array_filter($product->getMediaAttributeValues(), function ($value) {
                return $value !== self::EMPTY_IMAGE_VALUE && $value !== null;
            });
            foreach ($productMediaAttributes as $attributeCode => $value) {
                if ($attributeCode !== 'swatch_image') {
                    $baseImage = (string)$value;
                    break;
                }
            }
        }

        if (empty($baseImage)) {
            return [];
        }

        $resultGallery = $this->getAllSizeImages($product, $baseImage);
        $resultGallery['gallery'] = $this->getGalleryImages($product);

        return $resultGallery;
    }

    /**
     * @param ModelProduct $product
     * @return array
     */
    private function getGalleryImages(ModelProduct $product)
    {
        //TODO: remove after fix MAGETWO-48040
        $product = $this->productRepository->getById($product->getId());

        $result = [];
        $mediaGallery = $product->getMediaGalleryImages();
        foreach ($mediaGallery as $media) {
            $result[$media->getData('value_id')] = $this->getAllSizeImages(
                $product,
                $media->getData('file')
            );
        }
        return $result;
    }

    /**
     * @param ModelProduct $product
     * @param string $imageFile
     * @return array
     */
    private function getAllSizeImages(ModelProduct $product, $imageFile)
    {
        return [
            'large' => $this->imageHelper->init($product, 'product_page_image_large_no_frame')
                ->setImageFile($imageFile)
                ->getUrl(),
            'medium' => $this->imageHelper->init($product, 'product_page_image_medium_no_frame')
                ->setImageFile($imageFile)
                ->getUrl(),
            'small' => $this->imageHelper->init($product, 'product_page_image_small')
                ->setImageFile($imageFile)
                ->getUrl(),
        ];
    }

    /**
     * Retrieve collection of Swatch attributes
     *
     * @param Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    private function getSwatchAttributes(Product $product)
    {
        $attributes = $this->getAttributesFromConfigurable($product);
        $result = [];
        foreach ($attributes as $attribute) {
            if ($this->isSwatchAttribute($attribute)) {
                $result[] = $attribute;
            }
        }
        return $result;
    }

    /**
     * Retrieve collection of Eav Attributes from Configurable product
     *
     * @param Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    public function getAttributesFromConfigurable(Product $product)
    {
        $result = [];
        $typeInstance = $product->getTypeInstance();
        if ($typeInstance instanceof Configurable) {
            $configurableAttributes = $typeInstance->getConfigurableAttributes($product);
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $configurableAttribute */
            foreach ($configurableAttributes as $configurableAttribute) {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $attribute = $configurableAttribute->getProductAttribute();
                $result[] = $attribute;
            }
        }
        return $result;
    }

    /**
     * Retrieve all visible Swatch attributes for current product.
     *
     * @param Product $product
     * @return array
     */
    public function getSwatchAttributesAsArray(Product $product)
    {
        $result = [];
        $swatchAttributes = $this->getSwatchAttributes($product);
        foreach ($swatchAttributes as $swatchAttribute) {
            $swatchAttribute->setStoreId($this->storeManager->getStore()->getId());
            $attributeData = $swatchAttribute->getData();
            foreach ($swatchAttribute->getSource()->getAllOptions(false) as $option) {
                $attributeData['options'][$option['value']] = $option['label'];
            }
            $result[$attributeData['attribute_id']] = $attributeData;
        }

        return $result;
    }

    /**
     * Get swatch options by option id's according to fallback logic
     *
     * @param array $optionIds
     * @return array
     */
    public function getSwatchesByOptionsId(array $optionIds)
    {
        /** @var \Magento\Swatches\Model\ResourceModel\Swatch\Collection $swatchCollection */
        $swatchCollection = $this->swatchCollectionFactory->create();
        $swatchCollection->addFilterByOptionsIds($optionIds);

        $swatches = [];
        $currentStoreId = $this->storeManager->getStore()->getId();
        foreach ($swatchCollection as $item) {
            if ($item['type'] != Swatch::SWATCH_TYPE_TEXTUAL) {
                $swatches[$item['option_id']] = $item->getData();
            } elseif ($item['store_id'] == $currentStoreId && $item['value']) {
                $fallbackValues[$item['option_id']][$currentStoreId] = $item->getData();
            } elseif ($item['store_id'] == self::DEFAULT_STORE_ID) {
                $fallbackValues[$item['option_id']][self::DEFAULT_STORE_ID] = $item->getData();
            }
        }

        if (!empty($fallbackValues)) {
            $swatches = $this->addFallbackOptions($fallbackValues, $swatches);
        }

        return $swatches;
    }

    /**
     * @param array $fallbackValues
     * @param array $swatches
     * @return array
     */
    private function addFallbackOptions(array $fallbackValues, array $swatches)
    {
        $currentStoreId = $this->storeManager->getStore()->getId();
        foreach ($fallbackValues as $optionId => $optionsArray) {
            if (isset($optionsArray[$currentStoreId])) {
                $swatches[$optionId] = $optionsArray[$currentStoreId];
            } else {
                $swatches[$optionId] = $optionsArray[self::DEFAULT_STORE_ID];
            }
        }

        return $swatches;
    }

    /**
     * Check if the Product has Swatch attributes
     *
     * @param Product $product
     * @return bool
     */
    public function isProductHasSwatch(Product $product)
    {
        return sizeof($this->getSwatchAttributes($product));
    }

    /**
     * Check if an attribute is Swatch
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isSwatchAttribute(Attribute $attribute)
    {
        $result = $this->isVisualSwatch($attribute) || $this->isTextSwatch($attribute);
        return $result;
    }

    /**
     * Is attribute Visual Swatch
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isVisualSwatch(Attribute $attribute)
    {
        if (!$attribute->hasData(Swatch::SWATCH_INPUT_TYPE_KEY)) {
            $this->populateAdditionalDataEavAttribute($attribute);
        }
        return $attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) == Swatch::SWATCH_INPUT_TYPE_VISUAL;
    }

    /**
     * Is attribute Textual Swatch
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isTextSwatch(Attribute $attribute)
    {
        if (!$attribute->hasData(Swatch::SWATCH_INPUT_TYPE_KEY)) {
            $this->populateAdditionalDataEavAttribute($attribute);
        }
        return $attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) == Swatch::SWATCH_INPUT_TYPE_TEXT;
    }

    /**
     * Get product metadata pool.
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @deprecared
     */
    protected function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
