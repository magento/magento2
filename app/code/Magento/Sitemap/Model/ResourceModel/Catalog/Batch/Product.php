<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Model\ResourceModel\Catalog\Batch;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sitemap\Helper\Data as SitemapHelper;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductSelectBuilder;
use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Optimized sitemap resource product collection model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends AbstractDb
{
    private const NOT_SELECTED_IMAGE = 'no_selection';
    private const DEFAULT_BATCH_SIZE = 5000;
    private const MAX_IMAGES_PER_PRODUCT = 10;

    /**
     * @var Select|null
     */
    private ?Select $select = null;

    /**
     * @var array
     */
    private array $attributesCache = [];

    /**
     * @var ReadHandler
     */
    private ReadHandler $mediaGalleryReadHandler;

    /**
     * @var SitemapHelper
     */
    private SitemapHelper $sitemapHelper;

    /**
     * @var ProductResource
     */
    private ProductResource $productResource;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Visibility
     */
    private Visibility $productVisibility;

    /**
     * @var Status
     */
    private Status $productStatus;

    /**
     * @var Gallery
     */
    private Gallery $mediaGalleryResourceModel;

    /**
     * @var UrlBuilder|mixed
     */
    private UrlBuilder $imageUrlBuilder;

    /**
     * @var ProductSelectBuilder|mixed
     */
    private ProductSelectBuilder $productSelectBuilder;

    /**
     * @var int
     */
    private int $batchSize = self::DEFAULT_BATCH_SIZE;

    /**
     * @param Context $context
     * @param SitemapHelper $sitemapHelper
     * @param ProductResource $productResource
     * @param StoreManagerInterface $storeManager
     * @param Visibility $productVisibility
     * @param Status $productStatus
     * @param Gallery $mediaGalleryResourceModel
     * @param ReadHandler $mediaGalleryReadHandler
     * @param string|null $connectionName
     * @param UrlBuilder|null $imageUrlBuilder
     * @param ProductSelectBuilder|null $productSelectBuilder
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        SitemapHelper $sitemapHelper,
        ProductResource $productResource,
        StoreManagerInterface $storeManager,
        Visibility $productVisibility,
        Status $productStatus,
        Gallery $mediaGalleryResourceModel,
        ReadHandler $mediaGalleryReadHandler,
        ?string $connectionName = null,
        ?UrlBuilder $imageUrlBuilder = null,
        ?ProductSelectBuilder $productSelectBuilder = null
    ) {
        $this->sitemapHelper = $sitemapHelper;
        $this->productResource = $productResource;
        $this->storeManager = $storeManager;
        $this->productVisibility = $productVisibility;
        $this->productStatus = $productStatus;
        $this->mediaGalleryResourceModel = $mediaGalleryResourceModel;
        $this->mediaGalleryReadHandler = $mediaGalleryReadHandler;
        $this->imageUrlBuilder = $imageUrlBuilder ??
            ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->productSelectBuilder = $productSelectBuilder ??
            ObjectManager::getInstance()->get(ProductSelectBuilder::class);

        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Get product collection using memory-optimized streaming
     *
     * @param int|string $storeId
     * @return \Generator|false Returns generator that yields individual products
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollection($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        $this->prepareSelect($store);

        $connection = $this->getConnection();
        $query = $connection->query($this->prepareSelectStatement($this->select));

        $processedCount = 0;

        while ($row = $query->fetch()) {
            $product = $this->prepareProduct($row, (int) $store->getId());
            $processedCount++;

            yield $product;

            if ($processedCount % $this->batchSize === 0) {
                $this->cleanupMemory();
            }

            // Clear product reference
            unset($product);
        }
    }

    /**
     * Get product collection as array (for backward compatibility)
     *
     * @param int|string $storeId
     * @return array|false Returns array of all products
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollectionArray($storeId)
    {
        $products = [];
        $generator = $this->getCollection($storeId);

        if ($generator === false) {
            return false;
        }

        foreach ($generator as $product) {
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * Prepare select statement
     *
     * @param Store $store
     * @return void
     * @throws LocalizedException
     */
    private function prepareSelect(Store $store): void
    {
        $this->select = $this->productSelectBuilder->execute(
            $this->getMainTable(),
            $this->getIdFieldName(),
            $this->productResource->getLinkField(),
            $store
        );

        $this->addFilter((int) $store->getId(), 'visibility', $this->productVisibility->getVisibleInSiteIds(), 'in');
        $this->addFilter((int) $store->getId(), 'status', $this->productStatus->getVisibleStatusIds(), 'in');

        $imageIncludePolicy = $this->sitemapHelper->getProductImageIncludePolicy((int) $store->getId());
        if (IncludeImage::INCLUDE_NONE !== $imageIncludePolicy) {
            $this->joinAttribute((int) $store->getId(), 'name', 'name');
            if (IncludeImage::INCLUDE_ALL === $imageIncludePolicy) {
                $this->joinAttribute((int) $store->getId(), 'thumbnail', 'thumbnail');
            } elseif (IncludeImage::INCLUDE_BASE === $imageIncludePolicy) {
                $this->joinAttribute((int) $store->getId(), 'image', 'image');
            }
        }
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Select|false
     * @throws LocalizedException
     */
    private function addFilter(int $storeId, string $attributeCode, $value, string $type = '='): false|Select|null
    {
        if (!$this->select instanceof Select) {
            return false;
        }

        $conditionRule = match ($type) {
            '=' => '=?',
            'in' => ' IN(?)',
            default => false,
        };

        if ($conditionRule === false) {
            return false;
        }

        $attribute = $this->getAttribute($attributeCode);
        if ($attribute['backend_type'] === 'static') {
            $this->select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->joinAttribute($storeId, $attributeCode);
            if ($attribute['is_global']) {
                $this->select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $this->getConnection()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );
                $this->select->where('(' . $ifCase . ')' . $conditionRule, $value);
            }
        }

        return $this->select;
    }

    /**
     * Join attribute by code
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param string|null $column
     * @return void
     * @throws LocalizedException
     */
    private function joinAttribute(int $storeId, string $attributeCode, ?string $column = null): void
    {
        $connection = $this->getConnection();
        $attribute = $this->getAttribute($attributeCode);
        $linkField = $this->productResource->getLinkField();
        $attrTableAlias = 't1_' . $attributeCode;

        $this->select->joinLeft(
            [$attrTableAlias => $attribute['table']],
            "e.{$linkField} = {$attrTableAlias}.{$linkField}"
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.store_id = ?', Store::DEFAULT_STORE_ID)
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.attribute_id = ?', $attribute['attribute_id']),
            []
        );

        $columnValue = 't1_' . $attributeCode . '.value';

        if (!$attribute['is_global']) {
            $attrTableAlias2 = 't2_' . $attributeCode;
            $this->select->joinLeft(
                ['t2_' . $attributeCode => $attribute['table']],
                "{$attrTableAlias}.{$linkField} = {$attrTableAlias2}.{$linkField}"
                . ' AND ' . $attrTableAlias . '.attribute_id = ' . $attrTableAlias2 . '.attribute_id'
                . ' AND ' . $connection->quoteInto($attrTableAlias2 . '.store_id = ?', $storeId),
                []
            );
            $columnValue = $this->getConnection()->getIfNullSql('t2_' . $attributeCode . '.value', $columnValue);
        }

        if ($column !== null) {
            $this->select->columns([$column => $columnValue]);
        }
    }

    /**
     * Get attribute data by attribute code
     *
     * @param string $attributeCode
     * @return array
     * @throws LocalizedException
     */
    private function getAttribute(string $attributeCode): array
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->productResource->getAttribute($attributeCode);

            $this->attributesCache[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal() === ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend_type' => $attribute->getBackendType(),
            ];
        }
        return $this->attributesCache[$attributeCode];
    }

    /**
     * Prepare product
     *
     * @param array $productRow
     * @param int $storeId
     * @return DataObject
     * @throws LocalizedException
     */
    private function prepareProduct(array $productRow, int $storeId): DataObject
    {
        $product = new DataObject();

        if (isset($productRow[$this->getIdFieldName()])) {
            $product['id'] = $productRow[$this->getIdFieldName()];

            if (empty($productRow['url'])) {
                $productRow['url'] = 'catalog/product/view/id/' . $product['id'];
            }

            $product->addData($productRow);
            $this->loadProductImages($product, $storeId);
        }

        return $product;
    }

    /**
     * Load product images
     *
     * @param DataObject $product
     * @param int $storeId
     * @return void
     */
    private function loadProductImages(DataObject $product, int $storeId): void
    {
        $this->storeManager->setCurrentStore($storeId);
        $imageIncludePolicy = $this->sitemapHelper->getProductImageIncludePolicy($storeId);

        $imagesCollection = [];
        if (IncludeImage::INCLUDE_ALL === $imageIncludePolicy) {
            $imagesCollection = $this->getAllProductImages($product, $storeId);
        } elseif (IncludeImage::INCLUDE_BASE === $imageIncludePolicy &&
            $product->getImage() &&
            $product->getImage() !== self::NOT_SELECTED_IMAGE
        ) {
            $imagesCollection = [
                new DataObject(['url' => $this->getProductImageUrl($product->getImage())]),
            ];
        }

        if (!empty($imagesCollection)) {
            $thumbnail = $product->getThumbnail();
            if ($thumbnail && $thumbnail !== self::NOT_SELECTED_IMAGE) {
                $thumbnailUrl = $this->getProductImageUrl($thumbnail);
            } else {
                $thumbnailUrl = $imagesCollection[0]->getUrl();
            }

            $product->setImages(
                new DataObject([
                    'collection' => $imagesCollection,
                    'title' => $product->getName(),
                    'thumbnail' => $thumbnailUrl
                ])
            );
        }
    }

    /**
     * Get all product images with limit
     *
     * @param DataObject $product
     * @param int $storeId
     * @return array
     */
    private function getAllProductImages(DataObject $product, int $storeId): array
    {
        $product->setStoreId($storeId);
        $gallery = $this->mediaGalleryResourceModel->loadProductGalleryByAttributeId(
            $product,
            $this->mediaGalleryReadHandler->getAttribute()->getId()
        );

        $imagesCollection = [];
        if ($gallery) {
            $imageCount = 0;

            foreach ($gallery as $image) {
                if ($imageCount >= self::MAX_IMAGES_PER_PRODUCT) {
                    break;
                }

                $imagesCollection[] = new DataObject([
                    'url' => $this->getProductImageUrl($image['file']),
                    'caption' => $image['label'] ?: $image['label_default'],
                ]);
                $imageCount++;
            }
        }

        return $imagesCollection;
    }

    /**
     * Allow plugins to modify select statement
     *
     * @param Select $select
     * @return Select
     */
    public function prepareSelectStatement(Select $select): Select
    {
        return $select;
    }

    /**
     * Get product image URL
     *
     * @param string $image
     * @return string
     */
    private function getProductImageUrl(string $image): string
    {
        return $this->imageUrlBuilder->getUrl($image, 'product_page_image_large');
    }

    /**
     * Clean up memory
     *
     * @return void
     */
    private function cleanupMemory(): void
    {
        $this->select = null;
        $this->attributesCache = [];

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}
