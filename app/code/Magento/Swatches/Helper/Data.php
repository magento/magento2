<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Helper;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Swatches\Model\SwatchAttributeType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data
{
    /**
     * When we init media gallery empty image types contain this value.
     */
    public const EMPTY_IMAGE_VALUE = 'no_selection';

    /**
     * The int value of the Default store ID
     */
    public const DEFAULT_STORE_ID = 0;

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
     * Product metadata pool
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var SwatchAttributesProvider
     */
    private $swatchAttributesProvider;

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
     * @var array
     */
    private $swatchesCache = [];

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var SwatchAttributeType
     */
    private $swatchTypeChecker;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param UrlBuilder $urlBuilder
     * @param Json|null $serializer
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param SwatchAttributeType|null $swatchTypeChecker
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        SwatchCollectionFactory $swatchCollectionFactory,
        UrlBuilder $urlBuilder,
        Json $serializer = null,
        SwatchAttributesProvider $swatchAttributesProvider = null,
        SwatchAttributeType $swatchTypeChecker = null
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->imageUrlBuilder = $urlBuilder;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->create(Json::class);
        $this->swatchAttributesProvider = $swatchAttributesProvider
            ?: ObjectManager::getInstance()->get(SwatchAttributesProvider::class);
        $this->swatchTypeChecker = $swatchTypeChecker
            ?: ObjectManager::getInstance()->create(SwatchAttributeType::class);
    }

    /**
     * Assemble Additional Data for Eav Attribute
     *
     * @param Attribute $attribute
     * @return $this
     */
    public function assembleAdditionalDataEavAttribute(Attribute $attribute)
    {
        $initialAdditionalData = [];
        $additionalData = (string)$attribute->getData('additional_data');
        if (!empty($additionalData)) {
            $additionalData = $this->serializer->unserialize($additionalData);
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
        $attribute->setData('additional_data', $this->serializer->serialize($additionalData));
        return $this;
    }

    /**
     * Check is media attribute available
     *
     * @param Product $product
     * @param string $attributeCode
     * @return bool
     */
    private function isMediaAvailable(Product $product, string $attributeCode): bool
    {
        $isAvailable = false;

        $mediaGallery = $product->getMediaGalleryEntries();
        foreach ($mediaGallery as $mediaEntry) {
            if (in_array($attributeCode, $mediaEntry->getTypes(), true)) {
                $isAvailable = !$mediaEntry->isDisabled();
                break;
            }
        }

        return $isAvailable;
    }

    /**
     * Load first variation
     *
     * @param string $attributeCode swatch_image|image
     * @param Product $configurableProduct
     * @param array $requiredAttributes
     * @return bool|ProductInterface
     */
    private function loadFirstVariation($attributeCode, Product $configurableProduct, array $requiredAttributes)
    {
        if ($this->isProductHasSwatch($configurableProduct)) {
            $usedProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);

            foreach ($usedProducts as $simpleProduct) {
                if (!array_diff_assoc($requiredAttributes, $simpleProduct->getData())
                    && $this->isMediaAvailable($simpleProduct, $attributeCode)
                ) {
                    return $simpleProduct;
                }
            }
        }

        return false;
    }

    /**
     * Load first variation with swatch image
     *
     * @param ProductInterface|Product $configurableProduct
     * @param array $requiredAttributes
     * @return bool|ProductInterface
     */
    public function loadFirstVariationWithSwatchImage(ProductInterface $configurableProduct, array $requiredAttributes)
    {
        return $this->loadFirstVariation('swatch_image', $configurableProduct, $requiredAttributes);
    }

    /**
     * Load first variation with image
     *
     * @param ProductInterface|Product $configurableProduct
     * @param array $requiredAttributes
     * @return bool|ProductInterface
     */
    public function loadFirstVariationWithImage(ProductInterface $configurableProduct, array $requiredAttributes)
    {
        return $this->loadFirstVariation('image', $configurableProduct, $requiredAttributes);
    }

    /**
     * Load Variation Product using fallback
     *
     * @param ProductInterface $parentProduct
     * @param array $attributes
     * @return bool|ProductInterface
     */
    public function loadVariationByFallback(ProductInterface $parentProduct, array $attributes)
    {
        if (!$this->isProductHasSwatch($parentProduct)) {
            return false;
        }

        $productCollection = $this->productCollectionFactory->create();

        $productLinkedFiled = $this->getMetadataPool()
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
        $parentId = $parentProduct->getData($productLinkedFiled);

        $this->addFilterByParent($productCollection, $parentId);

        $configurableAttributes = $this->getAttributesFromConfigurable($parentProduct);

        $resultAttributesToFilter = [];
        foreach ($configurableAttributes as $attribute) {
            $attributeCode = $attribute->getData('attribute_code');
            if (array_key_exists($attributeCode, $attributes)) {
                $resultAttributesToFilter[$attributeCode] = $attributes[$attributeCode];
            }
        }

        $this->addFilterByAttributes($productCollection, $resultAttributesToFilter);

        $variationProduct = $productCollection->getFirstItem();
        if ($variationProduct && $variationProduct->getId()) {
            return $this->productRepository->getById($variationProduct->getId());
        }

        return false;
    }

    /**
     * Add filter by attribute
     *
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
     * Add filter by parent
     *
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
     *
     * Array structure: [
     *  ['image'] => 'http://url/media/catalog/product/2/0/blabla.jpg',
     *  ['mediaGallery'] => [
     *      galleryImageId1 => simpleProductImage1.jpg,
     *      galleryImageId2 => simpleProductImage2.jpg,
     *      ...,
     *      ]
     * ]
     *
     * @param Product $product
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductMediaGallery(Product $product): array
    {
        $baseImage = null;
        $gallery = [];

        $mediaGallery = $product->getMediaGalleryEntries();
        /** @var ProductAttributeMediaGalleryEntryInterface $mediaEntry */
        foreach ($mediaGallery as $mediaEntry) {
            if ($mediaEntry->isDisabled()) {
                continue;
            }
            if (!$baseImage || $this->isMainImage($mediaEntry)) {
                $baseImage = $mediaEntry;
            }

            $gallery[$mediaEntry->getId()] = $this->collectImageData($mediaEntry);
        }

        if (!$baseImage) {
            return [];
        }

        $resultGallery = $this->collectImageData($baseImage);
        $resultGallery['gallery'] = $gallery;

        return $resultGallery;
    }

    /**
     * Checks if image is main image in gallery
     *
     * @param ProductAttributeMediaGalleryEntryInterface $mediaEntry
     * @return bool
     */
    private function isMainImage(ProductAttributeMediaGalleryEntryInterface $mediaEntry): bool
    {
        return in_array('image', $mediaEntry->getTypes(), true);
    }

    /**
     * Returns image data for swatches
     *
     * @param ProductAttributeMediaGalleryEntryInterface $mediaEntry
     * @return array
     */
    private function collectImageData(ProductAttributeMediaGalleryEntryInterface $mediaEntry): array
    {
        $image = $this->getAllSizeImages($mediaEntry->getFile());
        $image[ProductAttributeMediaGalleryEntryInterface::POSITION] =  $mediaEntry->getPosition();
        $image['isMain'] =$this->isMainImage($mediaEntry);
        return $image;
    }

    /**
     * Get all size images
     *
     * @param string $imageFile
     * @return array
     */
    private function getAllSizeImages($imageFile)
    {
        return [
            'large' => $this->imageUrlBuilder->getUrl($imageFile, 'product_swatch_image_large'),
            'medium' => $this->imageUrlBuilder->getUrl($imageFile, 'product_swatch_image_medium'),
            'small' => $this->imageUrlBuilder->getUrl($imageFile, 'product_swatch_image_small')
        ];
    }

    /**
     * Retrieve collection of Swatch attributes
     *
     * @param ProductInterface|Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    private function getSwatchAttributes(ProductInterface $product)
    {
        return $this->swatchAttributesProvider->provide($product);
    }

    /**
     * Retrieve collection of Eav Attributes from Configurable product
     *
     * @param ProductInterface|Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    public function getAttributesFromConfigurable(ProductInterface $product)
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
     * @param ProductInterface $product
     * @return array
     */
    public function getSwatchAttributesAsArray(ProductInterface $product)
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
        $swatches = $this->getCachedSwatches($optionIds);

        if (count($swatches) !== count($optionIds)) {
            $swatchOptionIds = array_diff($optionIds, array_keys($swatches));
            /** @var \Magento\Swatches\Model\ResourceModel\Swatch\Collection $swatchCollection */
            $swatchCollection = $this->swatchCollectionFactory->create();
            $swatchCollection->addFilterByOptionsIds($swatchOptionIds);

            $swatches = [];
            $fallbackValues = [];
            $currentStoreId = $this->storeManager->getStore()->getId();
            foreach ($swatchCollection->getData() as $item) {
                if ($item['type'] != Swatch::SWATCH_TYPE_TEXTUAL) {
                    $swatches[$item['option_id']] = $item;
                } elseif ($item['store_id'] == $currentStoreId && $item['value'] != '') {
                    $fallbackValues[$item['option_id']][$currentStoreId] = $item;
                } elseif ($item['store_id'] == self::DEFAULT_STORE_ID) {
                    $fallbackValues[$item['option_id']][self::DEFAULT_STORE_ID] = $item;
                }
            }

            if (!empty($fallbackValues)) {
                $swatches = $this->addFallbackOptions($fallbackValues, $swatches);
            }
            $this->setCachedSwatches($swatchOptionIds, $swatches);
        }

        return array_filter($this->getCachedSwatches($optionIds));
    }

    /**
     * Get cached swatches
     *
     * @param array $optionIds
     * @return array
     */
    private function getCachedSwatches(array $optionIds)
    {
        return array_intersect_key($this->swatchesCache, array_combine($optionIds, $optionIds));
    }

    /**
     * Cache swatch. If no swathes found for specific option id - set null for prevent double call
     *
     * @param array $optionIds
     * @param array $swatches
     * @return void
     */
    private function setCachedSwatches(array $optionIds, array $swatches)
    {
        foreach ($optionIds as $optionId) {
            $this->swatchesCache[$optionId] = $swatches[$optionId] ?? null;
        }
    }

    /**
     * Add fallback options
     *
     * @param array $fallbackValues
     * @param array $swatches
     * @return array
     */
    private function addFallbackOptions(array $fallbackValues, array $swatches)
    {
        $currentStoreId = $this->storeManager->getStore()->getId();
        foreach ($fallbackValues as $optionId => $optionsArray) {
            if (isset($optionsArray[$currentStoreId]['type'], $swatches[$optionId]['type'])
                && $swatches[$optionId]['type'] === $optionsArray[$currentStoreId]['type']
            ) {
                $swatches[$optionId] = $optionsArray[$currentStoreId];
            } elseif (isset($optionsArray[$currentStoreId])) {
                $swatches[$optionId] = $optionsArray[$currentStoreId];
            } elseif (isset($optionsArray[self::DEFAULT_STORE_ID])) {
                $swatches[$optionId] = $optionsArray[self::DEFAULT_STORE_ID];
            }
        }

        return $swatches;
    }

    /**
     * Check if the Product has Swatch attributes
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function isProductHasSwatch(ProductInterface $product)
    {
        return !empty($this->getSwatchAttributes($product));
    }

    /**
     * Check if an attribute is Swatch
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isSwatchAttribute(Attribute $attribute)
    {
        return $this->swatchTypeChecker->isSwatchAttribute($attribute);
    }

    /**
     * Is attribute Visual Swatch
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isVisualSwatch(Attribute $attribute)
    {
        return $this->swatchTypeChecker->isVisualSwatch($attribute);
    }

    /**
     * Is attribute Textual Swatch
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isTextSwatch(Attribute $attribute)
    {
        return $this->swatchTypeChecker->isTextSwatch($attribute);
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
