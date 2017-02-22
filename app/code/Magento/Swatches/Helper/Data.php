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
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Default store ID
     */
    const DEFAULT_STORE_ID = 0;

    /**
     * Catalog\product area inside media folder
     */
    const  CATALOG_PRODUCT_MEDIA_PATH = 'catalog/product';

    /**
     * Current model
     *
     * @var \Magento\Swatches\Model\Query
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @deprecated
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Swatch collection factory
     *
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
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param Image $imageHelper
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Configurable $configurable,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        SwatchCollectionFactory $swatchCollectionFactory,
        Image $imageHelper
    ) {
        $this->productCollectionFactory   = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->storeManager = $storeManager;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->imageHelper = $imageHelper;

        parent::__construct($context);
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
    public function populateAdditionalDataEavAttribute(Attribute $attribute)
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
     * @deprecated
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @param string|int $attributeValue
     * @return \Magento\Catalog\Model\Product|null
     * @throws InputException
     */
    public function loadFirstVariationWithSwatchImage($product, $attributeCode, $attributeValue)
    {
        $product = $this->createSwatchProduct($product);
        if (!$product) {
            return null;
        }

        $products = $product->getTypeInstance()->getUsedProducts($product);
        $variationProduct = null;
        foreach ($products as $item) {
            if ($item->getData($attributeCode) != $attributeValue) {
                continue;
            }
            $media = $this->getProductMedia($item);
            if (!empty($media) && isset($media['swatch_image'])) {
                $variationProduct = $item;
                break;
            }
            if ($variationProduct !== false) {
                if (! empty($media) && isset($media['image'])) {
                    $variationProduct = $item;
                } else {
                    $variationProduct = false;
                }
            }
        }

        return $variationProduct;
    }

    /**
     * @param Product $configurableProduct
     * @param array $requiredAttributes
     * @return bool|Product
     */
    public function loadFirstVariationSwatchImage(Product $configurableProduct, array $requiredAttributes)
    {
        return $this->loadFirstVariation('swatch_image', $configurableProduct, $requiredAttributes);
    }

    /**
     * Load Variation Product using fallback
     *
     * @param Product|integer $parentProduct
     * @param array $attributes
     * @return bool|Product
     */
    public function loadVariationByFallback($parentProduct, array $attributes)
    {
        $parentProduct = $this->createSwatchProduct($parentProduct);
        if (!$parentProduct) {
            return false;
        }

        $productCollection = $this->productCollectionFactory->create();
        $this->addFilterByParent($productCollection, $parentProduct->getId());

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
     * @deprecated
     *
     * @param Product|integer $product
     * @param array $attributes
     * @return Product|bool|null
     */
    public function loadFirstVariationWithImage($product, array $attributes)
    {
        $product = $this->createSwatchProduct($product);
        if (! $product) {
            return false;
        }

        $productCollection = $this->prepareVariationCollection($product, $attributes);

        $variationProduct = false;
        foreach ($productCollection as $item) {
            $currentProduct = $this->productRepository->getById($item->getId());
            $media = $this->getProductMedia($currentProduct);
            if (!empty($media) && isset($media['image'])) {
                $variationProduct = $currentProduct;
                break;
            }
        }

        return $variationProduct;
    }

    /**
     * @param Product $configurableProduct
     * @param array $requiredAttributes
     * @return bool|Product
     */
    public function loadFirstVariationImage(Product $configurableProduct, array $requiredAttributes)
    {
        return $this->loadFirstVariation('image', $configurableProduct, $requiredAttributes);
    }

    /**
     * @deprecated
     *
     * @param Product $parentProduct
     * @param array $attributes
     * @return bool|ProductCollection
     * @throws InputException
     */
    protected function prepareVariationCollection(Product $parentProduct, array $attributes)
    {
        $productCollection = $this->productCollectionFactory->create();
        $this->addFilterByParent($productCollection, $parentProduct->getId());

        $configurableAttributes = $this->getAttributesFromConfigurable($parentProduct);
        foreach ($configurableAttributes as $attribute) {
            $productCollection->addAttributeToSelect($attribute['attribute_code']);
        }

        $this->addFilterByAttributes($productCollection, $attributes);

        return $productCollection;
    }

    /**
     * @param ProductCollection $productCollection
     * @param array $attributes
     * @return void
     */
    protected function addFilterByAttributes(ProductCollection $productCollection, array $attributes)
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
    protected function addFilterByParent(ProductCollection $productCollection, $parentId)
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
     * Get all media for product
     *
     * @deprecated
     *
     * @param Product $product
     * @return array|bool
     */
    protected function getProductMedia(Product $product)
    {
        return array_filter(
            $product->getMediaAttributeValues(),
            function ($value) {
                return $value != 'no_selection' && $value !== null;
            }
        );
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
     * @param Product $product
     * @return array
     */
    public function getProductMediaGallery (Product $product)
    {
        if (!in_array($product->getData('image'), [null, 'no_selection'], true)) {
            $baseImage = $product->getData('image');
        } else {
            $productMediaAttributes = array_filter($product->getMediaAttributeValues(), function ($value) {
                return $value !== 'no_selection' && $value !== null;
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
     * @param Product $product
     * @return array
     */
    protected function getGalleryImages(Product $product)
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
     * @param Product $product
     * @param string $imageFile
     * @return array
     */
    protected function getAllSizeImages(Product $product, $imageFile)
    {
        return [
            'large' => $this->imageHelper->init($product, 'product_page_image_large')
                ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                ->setImageFile($imageFile)
                ->getUrl(),
            'medium' => $this->imageHelper->init($product, 'product_page_image_medium')
                ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
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
    public function getSwatchAttributes(Product $product)
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
     * Load Product instance if required and check for proper instance type.
     *
     * @deprecated
     *
     * @param int|Product $product
     * @return Product
     * @throws InputException
     */
    protected function createSwatchProduct($product)
    {
        if (gettype($product) == 'integer') {
            $product = $this->productRepository->getById($product);
        } elseif (! ($product instanceof Product)) {
            throw new InputException(
                __('Swatch Helper: not valid parameter for product instantiation')
            );
        }
        if ($this->isProductHasSwatch($product)) {
            return $product;
        }
        return false;
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
                if (!in_array($simpleProduct->getData($attributeCode), [null, 'no_selection'], true)
                    && !array_diff_assoc($requiredAttributes, $simpleProduct->getData())
                ) {
                    return $simpleProduct;
                }
            }
        }

        return false;
    }
}
