<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

/**
 * Class provides iterator through number of products suitable for fulltext indexation
 *
 * To be suitable for fulltext index product must meet set of requirements:
 * - to be visible on frontend
 * - to be enabled
 * - in case product is composite at least one sub product must be visible and enabled
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full
{
    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * Searchable attributes cache
     *
     * @var \Magento\Eav\Model\Entity\Attribute[]
     */
    protected $searchableAttributes;

    /**
     * Index values separator
     *
     * @var string
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::$separator
     */
    protected $separator = ' | ';

    /**
     * Array of \DateTime objects per store
     *
     * @var \DateTime[]
     * @deprecated Not used anymore
     */
    protected $dates = [];

    /**
     * Product Type Instances cache
     *
     * @var array
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::$productTypes
     */
    protected $productTypes = [];

    /**
     * Product Emulators cache
     *
     * @var array
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::$productEmulators
     */
    protected $productEmulators = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $productAttributeCollectionFactory;

    /**
     * Catalog product status
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $catalogProductStatus;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * Catalog product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::$catalogProductType
     */
    protected $catalogProductType;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated Not used anymore
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::$storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Engine
     */
    protected $engine;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\IndexerInterface
     * @deprecated As part of self::cleanIndex()
     */
    protected $indexHandler;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @deprecated Not used anymore
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @deprecated Not used anymore
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @deprecated Not used anymore
     */
    protected $localeDate;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext
     * @deprecated Not used anymore
     */
    protected $fulltextResource;

    /**
     * @var \Magento\Framework\Search\Request\Config
     * @deprecated As part of self::reindexAll()
     */
    protected $searchRequestConfig;

    /**
     * @var \Magento\Framework\Search\Request\DimensionFactory
     * @deprecated As part of self::cleanIndex()
     */
    private $dimensionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\IndexIteratorFactory
     * @deprecated DataProvider used directly without IndexIterator
     * @see self::$dataProvider
     */
    private $iteratorFactory;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Search\Request\Config $searchRequestConfig
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory
     * @param \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider
     * @param \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory $indexHandlerFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext $fulltextResource
     * @param \Magento\Framework\Search\Request\DimensionFactory $dimensionFactory
     * @param \Magento\Framework\Indexer\ConfigInterface $indexerConfig
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\IndexIteratorFactory $indexIteratorFactory
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param DataProvider $dataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Search\Request\Config $searchRequestConfig,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory,
        \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider,
        \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory $indexHandlerFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext $fulltextResource,
        \Magento\Framework\Search\Request\DimensionFactory $dimensionFactory,
        \Magento\Framework\Indexer\ConfigInterface $indexerConfig,
        \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\IndexIteratorFactory $indexIteratorFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        DataProvider $dataProvider = null
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->catalogProductType = $catalogProductType;
        $this->eavConfig = $eavConfig;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->catalogProductStatus = $catalogProductStatus;
        $this->productAttributeCollectionFactory = $prodAttributeCollectionFactory;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->engine = $engineProvider->get();
        $configData = $indexerConfig->getIndexer(Fulltext::INDEXER_ID);
        $this->indexHandler = $indexHandlerFactory->create(['data' => $configData]);
        $this->dateTime = $dateTime;
        $this->localeResolver = $localeResolver;
        $this->localeDate = $localeDate;
        $this->fulltextResource = $fulltextResource;
        $this->dimensionFactory = $dimensionFactory;
        $this->iteratorFactory = $indexIteratorFactory;
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        $this->dataProvider = $dataProvider ?: ObjectManager::getInstance()->get(DataProvider::class);
    }

    /**
     * Rebuild whole fulltext index for all stores
     *
     * @deprecated Please use \Magento\CatalogSearch\Model\Indexer\Fulltext::executeFull instead
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext::executeFull
     * @return void
     */
    public function reindexAll()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        foreach ($storeIds as $storeId) {
            $this->cleanIndex($storeId);
            $this->rebuildStoreIndex($storeId);
        }
        $this->searchRequestConfig->reset();
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * Get parents IDs of product IDs to be re-indexed
     *
     * @param int[] $entityIds
     * @return int[]
     */
    protected function getProductIdsFromParents(array $entityIds)
    {
        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $fieldForParent = $metadata->getLinkField();

        $select = $this->connection
            ->select()
            ->from(['relation' => $this->getTable('catalog_product_relation')], [])
            ->distinct(true)
            ->where('child_id IN (?)', $entityIds)
            ->where('parent_id NOT IN (?)', $entityIds)
            ->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'relation.parent_id = cpe.' . $fieldForParent,
                ['cpe.entity_id']
            );
        return $this->connection->fetchCol($select);
    }

    /**
     * Regenerate search index for specific store
     *
     * To be suitable for indexation product must meet set of requirements:
     * - to be visible on frontend
     * - to be enabled
     * - in case product is composite at least one sub product must be enabled
     *
     * @param int $storeId Store View Id
     * @param int[] $productIds Product Entity Id
     * @return \Generator
     */
    public function rebuildStoreIndex($storeId, $productIds = null)
    {
        if ($productIds !== null) {
            $productIds = array_unique(array_merge($productIds, $this->getProductIdsFromParents($productIds)));
        }

        // prepare searchable attributes
        $staticFields = [];
        foreach ($this->getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }

        $dynamicFields = [
            'int' => array_keys($this->dataProvider->getSearchableAttributes('int')),
            'varchar' => array_keys($this->dataProvider->getSearchableAttributes('varchar')),
            'text' => array_keys($this->dataProvider->getSearchableAttributes('text')),
            'decimal' => array_keys($this->dataProvider->getSearchableAttributes('decimal')),
            'datetime' => array_keys($this->dataProvider->getSearchableAttributes('datetime')),
        ];

        $lastProductId = 0;
        $products = $this->dataProvider
            ->getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);
        while (count($products) > 0) {

            $productsIds = array_column($products, 'entity_id');
            $relatedProducts = $this->getRelatedProducts($products);
            $productsIds = array_merge($productsIds, array_values($relatedProducts));

            $productsAttributes = $this->dataProvider->getProductAttributes($storeId, $productsIds, $dynamicFields);

            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];

                if (!$this->isProductVisible($productData['entity_id'], $productsAttributes) ||
                    !$this->isProductEnabled($productData['entity_id'], $productsAttributes)
                ) {
                    continue;
                }

                $productIndex = [$productData['entity_id'] => $productsAttributes[$productData['entity_id']]];
                if (isset($relatedProducts[$productData['entity_id']])) {
                    $childProductsIndex = $this->getChildProductsIndex(
                        $productData['entity_id'],
                        $relatedProducts,
                        $productsAttributes
                    );
                    if (empty($childProductsIndex)) {
                        continue;
                    }
                    $productIndex = $productIndex + $childProductsIndex;
                }

                $index = $this->dataProvider->prepareProductIndex($productIndex, $productData, $storeId);
                yield $productData['entity_id'] => $index;
            }
            $products = $this->dataProvider
                ->getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);
        };
    }

    /**
     * Get related (child) products ids
     *
     * Load related (child) products ids for composite product type
     *
     * @param array $products
     * @return array
     */
    private function getRelatedProducts($products)
    {
        $relatedProducts = [];
        foreach ($products as $productData) {
            $relatedProducts[$productData['entity_id']] = $this->dataProvider->getProductChildIds(
                $productData['entity_id'],
                $productData['type_id']
            );
        }
        return array_filter($relatedProducts);
    }

    /**
     * Performs check that product is visible on Store Front
     *
     * Check that product is visible on Store Front using visibility attribute
     * and allowed visibility values.
     *
     * @param int $productId
     * @param array $productsAttributes
     * @return bool
     */
    private function isProductVisible($productId, array $productsAttributes)
    {
        $visibility = $this->dataProvider->getSearchableAttribute('visibility');
        $allowedVisibility = $this->engine->getAllowedVisibility();
        return isset($productsAttributes[$productId]) &&
            isset($productsAttributes[$productId][$visibility->getId()]) &&
            in_array($productsAttributes[$productId][$visibility->getId()], $allowedVisibility);
    }

    /**
     * Performs check that product is enabled on Store Front
     *
     * Check that product is enabled on Store Front using status attribute
     * and statuses allowed to be visible on Store Front.
     *
     * @param int $productId
     * @param array $productsAttributes
     * @return bool
     */
    private function isProductEnabled($productId, array $productsAttributes)
    {
        $status = $this->dataProvider->getSearchableAttribute('status');
        $allowedStatuses = $this->catalogProductStatus->getVisibleStatusIds();
        return isset($productsAttributes[$productId]) &&
            isset($productsAttributes[$productId][$status->getId()]) &&
            in_array($productsAttributes[$productId][$status->getId()], $allowedStatuses);
    }

    /**
     * Get data for index using related(child) products data
     *
     * Build data for index using child products(if any).
     * Use only enabled child products {@see isProductEnabled}.
     *
     * @param int $parentId
     * @param array $relatedProducts
     * @param array $productsAttributes
     * @return array
     */
    private function getChildProductsIndex($parentId, array $relatedProducts, array $productsAttributes)
    {
        $productIndex = [];
        foreach ($relatedProducts[$parentId] as $productChildId) {
            if ($this->isProductEnabled($productChildId, $productsAttributes)) {
                $productIndex[$productChildId] = $productsAttributes[$productChildId];
            }
        }
        return $productIndex;
    }

    /**
     * Clean search index data for store
     *
     * @deprecated As part of self::reindexAll()
     * @param int $storeId
     * @return void
     */
    protected function cleanIndex($storeId)
    {
        $dimension = $this->dimensionFactory->create(['name' => self::SCOPE_FIELD_NAME, 'value' => $storeId]);
        $this->indexHandler->cleanIndex([$dimension]);
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return \Magento\Eav\Model\Config
     * @deprecated Use $self::$eavConfig directly
     */
    protected function getEavConfig()
    {
        return $this->eavConfig;
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     * @deprecated see DataProvider::getSearchableAttributes()
     * @return \Magento\Eav\Model\Entity\Attribute[]
     */
    protected function getSearchableAttributes($backendType = null)
    {
        return $this->dataProvider->getSearchableAttributes($backendType);
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     * @deprecated see DataProvider::getSearchableAttributes()
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    protected function getSearchableAttribute($attribute)
    {
        return $this->dataProvider->getSearchableAttribute($attribute);
    }

    /**
     * Returns expression for field unification
     *
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::unifyField()
     * @param string $field
     * @param string $backendType
     * @return \Zend_Db_Expr
     */
    protected function unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->connection->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    /**
     * Retrieve Product Type Instance
     *
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::getProductTypeInstance()
     * @param string $typeId
     * @return \Magento\Catalog\Model\Product\Type\AbstractType
     */
    protected function getProductTypeInstance($typeId)
    {
        if (!isset($this->productTypes[$typeId])) {
            $productEmulator = $this->getProductEmulator($typeId);

            $this->productTypes[$typeId] = $this->catalogProductType->factory($productEmulator);
        }
        return $this->productTypes[$typeId];
    }

    /**
     * Retrieve Product Emulator (Magento Object)
     *
     * @deprecated Moved to \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::getProductEmulator()
     * @param string $typeId
     * @return \Magento\Framework\DataObject
     */
    protected function getProductEmulator($typeId)
    {
        if (!isset($this->productEmulators[$typeId])) {
            $productEmulator = new \Magento\Framework\DataObject();
            $productEmulator->setTypeId($typeId);
            $this->productEmulators[$typeId] = $productEmulator;
        }
        return $this->productEmulators[$typeId];
    }
}
