<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Full
{
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
     */
    protected $separator = ' | ';

    /**
     * Array of \Magento\Framework\Stdlib\DateTime\DateInterface objects per store
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $productTypes = [];

    /**
     * Product Emulators cache
     *
     * @var array
     */
    protected $productEmulators = [];

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
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
     */
    protected $catalogProductType;

    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $catalogSearchData;

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
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\EngineProvider
     */
    protected $engineProvider;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Fulltext
     */
    protected $fulltextResource;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $searchRequestConfig;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Search\Request\Config $searchRequestConfig
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
     * @param \Magento\CatalogSearch\Model\Resource\EngineProvider $engineProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogSearch\Model\Resource\Fulltext $fulltextResource
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Search\Request\Config $searchRequestConfig,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $productAttributeCollectionFactory,
        \Magento\CatalogSearch\Model\Resource\EngineProvider $engineProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogSearch\Model\Resource\Fulltext $fulltextResource,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->resource = $resource;
        $this->catalogProductType = $catalogProductType;
        $this->eavConfig = $eavConfig;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->catalogProductStatus = $catalogProductStatus;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->eventManager = $eventManager;
        $this->catalogSearchData = $catalogSearchData;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->engineProvider = $engineProvider;
        $this->dateTime = $dateTime;
        $this->localeResolver = $localeResolver;
        $this->localeDate = $localeDate;
        $this->fulltextResource = $fulltextResource;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Rebuild whole fulltext index
     *
     * @return void
     */
    public function reindexAll()
    {
        $this->rebuildIndex();
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
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getReadAdapter()
    {
        $writeAdapter = $this->getWriteAdapter();
        if ($writeAdapter && $writeAdapter->getTransactionLevel() > 0) {
            // if transaction is started we should use write connection for reading
            return $writeAdapter;
        }
        return $this->resource->getConnection('read');
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getWriteAdapter()
    {
        return $this->resource->getConnection('write');
    }

    /**
     * Regenerate search index for store(s)
     *
     * @param int|array|null $productIds
     * @return void
     */
    protected function rebuildIndex($productIds = null)
    {
        $storeIds = array_keys($this->storeManager->getStores());
        foreach ($storeIds as $storeId) {
            $this->rebuildStoreIndex($storeId, $productIds);
        }
        $this->searchRequestConfig->reset();
    }

    /**
     * Regenerate search index for specific store
     *
     * @param int $storeId Store View Id
     * @param int|array $productIds Product Entity Id
     * @return void
     */
    protected function rebuildStoreIndex($storeId, $productIds = null)
    {
        $this->cleanIndex($storeId, $productIds);

        // prepare searchable attributes
        $staticFields = [];
        foreach ($this->getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        $dynamicFields = [
            'int' => array_keys($this->getSearchableAttributes('int')),
            'varchar' => array_keys($this->getSearchableAttributes('varchar')),
            'text' => array_keys($this->getSearchableAttributes('text')),
            'decimal' => array_keys($this->getSearchableAttributes('decimal')),
            'datetime' => array_keys($this->getSearchableAttributes('datetime')),
        ];

        // status and visibility filter
        $visibility = $this->getSearchableAttribute('visibility');
        $status = $this->getSearchableAttribute('status');
        $statusIds = $this->catalogProductStatus->getVisibleStatusIds();
        $allowedVisibility = $this->engineProvider->get()->getAllowedVisibility();

        $lastProductId = 0;
        while (true) {
            $products = $this->getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);
            if (!$products) {
                break;
            }

            $productAttributes = [];
            $productRelations = [];
            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChildren = $this->getProductChildIds($productData['entity_id'], $productData['type_id']);
                $productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }

            $productIndexes = [];
            $productAttributes = $this->getProductAttributes($storeId, $productAttributes, $dynamicFields);
            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];
                if (!isset($productAttr[$visibility->getId()])
                    || !in_array($productAttr[$visibility->getId()], $allowedVisibility)
                ) {
                    continue;
                }
                if (!isset($productAttr[$status->getId()])
                    || !in_array($productAttr[$status->getId()], $statusIds)
                ) {
                    continue;
                }

                $productIndex = [$productData['entity_id'] => $productAttr];

                $hasChildren = false;
                $productChildren = $productRelations[$productData['entity_id']];
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        if (isset($productAttributes[$productChildId])) {
                            $productChildAttr = $productAttributes[$productChildId];
                            if (!isset($productChildAttr[$status->getId()])
                                || !in_array($productChildAttr[$status->getId()], $statusIds)
                            ) {
                                continue;
                            }

                            $hasChildren = true;
                            $productIndex[$productChildId] = $productChildAttr;
                        }
                    }
                }
                if (!is_null($productChildren) && !$hasChildren) {
                    continue;
                }

                $index = $this->prepareProductIndex($productIndex, $productData, $storeId);

                $productIndexes[$productData['entity_id']] = $index;
            }

            $this->saveProductIndexes($storeId, $productIndexes);
        }

        $this->fulltextResource->resetSearchResults();
    }

    /**
     * Retrieve searchable products per store
     *
     * @param int $storeId
     * @param array $staticFields
     * @param array|int $productIds
     * @param int $lastProductId
     * @param int $limit
     * @return array
     */
    protected function getSearchableProducts(
        $storeId,
        array $staticFields,
        $productIds = null,
        $lastProductId = 0,
        $limit = 100
    ) {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $writeAdapter = $this->getWriteAdapter();

        $select = $writeAdapter->select()
            ->useStraightJoin(true)
            ->from(
                ['e' => $this->getTable('catalog_product_entity')],
                array_merge(['entity_id', 'type_id'], $staticFields)
            )
            ->join(
                ['website' => $this->getTable('catalog_product_website')],
                $writeAdapter->quoteInto('website.product_id = e.entity_id AND website.website_id = ?', $websiteId),
                []
            );

        if (!is_null($productIds)) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $select->where('e.entity_id > ?', $lastProductId)->limit($limit)->order('e.entity_id');

        $result = $writeAdapter->fetchAll($select);

        return $result;
    }

    /**
     * Delete search index data for store
     *
     * @param int $storeId Store View Id
     * @param int $productId Product Entity Id
     * @return void
     */
    protected function cleanIndex($storeId = null, $productId = null)
    {
        if ($this->engineProvider->get()) {
            $this->engineProvider->get()->cleanIndex($storeId, $productId);
        }
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return \Magento\Eav\Model\Config
     */
    protected function getEavConfig()
    {
        return $this->eavConfig;
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     * @return \Magento\Eav\Model\Entity\Attribute[]
     */
    protected function getSearchableAttributes($backendType = null)
    {
        if (null === $this->searchableAttributes) {
            $this->searchableAttributes = [];

            $productAttributes = $this->productAttributeCollectionFactory->create();

            if ($this->engineProvider->get() && $this->engineProvider->get()->allowAdvancedIndex()) {
                $productAttributes->addToIndexFilter(true);
            } else {
                $productAttributes->addSearchableAttributeFilter();
            }
            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $productAttributes->getItems();

            $this->eventManager->dispatch(
                'catelogsearch_searchable_attributes_load_after',
                ['engine' => $this->engineProvider->get(), 'attributes' => $attributes]
            );

            $entity = $this->getEavConfig()->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->searchableAttributes = $attributes;
        }

        if (!is_null($backendType)) {
            $attributes = [];
            foreach ($this->searchableAttributes as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
        }

        return $this->searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    protected function getSearchableAttribute($attribute)
    {
        $attributes = $this->getSearchableAttributes();
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
        }

        return $this->getEavConfig()->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
    }

    /**
     * Returns expression for field unification
     *
     * @param string $field
     * @param string $backendType
     * @return \Zend_Db_Expr
     */
    protected function unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->getReadAdapter()->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    /**
     * Load product(s) attributes
     *
     * @param int $storeId
     * @param array $productIds
     * @param array $attributeTypes
     * @return array
     */
    protected function getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        $result = [];
        $selects = [];
        $adapter = $this->getWriteAdapter();
        $ifStoreValue = $adapter->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable('catalog_product_entity_' . $backendType);
                $selects[] = $adapter->select()->from(
                    ['t_default' => $tableName],
                    ['entity_id', 'attribute_id']
                )->joinLeft(
                    ['t_store' => $tableName],
                    $adapter->quoteInto(
                        't_default.entity_id=t_store.entity_id' .
                        ' AND t_default.attribute_id=t_store.attribute_id' .
                        ' AND t_store.store_id = ?',
                        $storeId
                    ),
                    ['value' => $this->unifyField($ifStoreValue, $backendType)]
                )->where(
                    't_default.store_id = ?',
                    0
                )->where(
                    't_default.attribute_id IN (?)',
                    $attributeIds
                )->where(
                    't_default.entity_id IN (?)',
                    $productIds
                );
            }
        }

        if ($selects) {
            $select = $adapter->select()->union($selects, \Zend_Db_Select::SQL_UNION_ALL);
            $query = $adapter->query($select);
            while ($row = $query->fetch()) {
                $result[$row['entity_id']][$row['attribute_id']] = $row['value'];
            }
        }

        return $result;
    }

    /**
     * Retrieve Product Type Instance
     *
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
     * Return all product children ids
     *
     * @param int $productId Product Entity Id
     * @param string $typeId Super Product Link Type
     * @return array|null
     */
    protected function getProductChildIds($productId, $typeId)
    {
        $typeInstance = $this->getProductTypeInstance($typeId);
        $relation = $typeInstance->isComposite(
            $this->getProductEmulator($typeId)
        ) ? $typeInstance->getRelationInfo() : false;

        if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
            $select = $this->getReadAdapter()->select()->from(
                ['main' => $this->getTable($relation->getTable())],
                [$relation->getChildFieldName()]
            )->where(
                $relation->getParentFieldName() . ' = ?',
                $productId
            );
            if (!is_null($relation->getWhere())) {
                $select->where($relation->getWhere());
            }
            return $this->getReadAdapter()->fetchCol($select);
        }

        return null;
    }

    /**
     * Retrieve Product Emulator (Magento Object)
     *
     * @param string $typeId
     * @return \Magento\Framework\Object
     */
    protected function getProductEmulator($typeId)
    {
        if (!isset($this->productEmulators[$typeId])) {
            $productEmulator = new \Magento\Framework\Object();
            $productEmulator->setIdFieldName('entity_id')->setTypeId($typeId);
            $this->productEmulators[$typeId] = $productEmulator;
        }
        return $this->productEmulators[$typeId];
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return string
     */
    protected function prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = [];

        foreach ($this->getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                $value = $this->getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    if (isset($index[$attributeCode])) {
                        if (!is_array($index[$attributeCode])) {
                            $index[$attributeCode] = [$index[$attributeCode]];
                        }
                        $index[$attributeCode][] = $value;
                    } else {
                        $index[$attributeCode] = $value;
                    }
                }
            }
        }

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {
                $value = $this->getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!is_null($value) && $value !== false) {
                    $attributeCode = $this->getSearchableAttribute($attributeId)->getAttributeCode();

                    if (isset($index[$attributeCode])) {
                        $index[$attributeCode][$entityId] = $value;
                    } else {
                        $index[$attributeCode] = [$entityId => $value];
                    }
                }
            }
        }

        if ($this->engineProvider->get()->allowAdvancedIndex()) {
            $product = $this->getProductEmulator(
                $productData['type_id']
            )->setId(
                $productData['entity_id']
            )->setStoreId(
                $storeId
            );
            $typeInstance = $this->getProductTypeInstance($productData['type_id']);
            $data = $typeInstance->getSearchableData($product);
            if ($data) {
                $index['options'] = $data;
            }
        }

        if ($this->engineProvider->get()) {
            return $this->engineProvider->get()->prepareEntityIndex($index, $this->separator);
        }

        return $this->catalogSearchData->prepareIndexdata($index, $this->separator);
    }

    /**
     * Retrieve attribute source value for search
     *
     * @param int $attributeId
     * @param mixed $valueId
     * @param int $storeId
     * @return mixed
     */
    protected function getAttributeValue($attributeId, $valueId, $storeId)
    {
        $attribute = $this->getSearchableAttribute($attributeId);
        $value = $this->engineProvider->get()->processAttributeValue($attribute, $valueId);

        if ($attribute->getIsSearchable()
            && $attribute->usesSource()
        ) {
            $attribute->setStoreId($storeId);
            $valueText = $attribute->getSource()->getIndexOptionText($valueId);

            if (is_array($valueText)) {
                $value .=  $this->separator . implode($this->separator, $valueText);
            } else {
                $value .= $this->separator . $valueText;
            }
        }

        $value = preg_replace('/\\s+/siu', ' ', trim(strip_tags($value)));

        return $value;
    }

    /**
     * Save Multiply Product indexes
     *
     * @param int $storeId
     * @param array $productIndexes
     * @return $this
     */
    protected function saveProductIndexes($storeId, $productIndexes)
    {
        if ($this->engineProvider->get()) {
            $this->engineProvider->get()->saveEntityIndexes($storeId, $productIndexes);
        }

        return $this;
    }

    /**
     * Retrieve Date value for store
     *
     * @param int $storeId
     * @param string $date
     * @return string|null
     */
    protected function getStoreDate($storeId, $date = null)
    {
        if (!isset($this->dates[$storeId])) {
            $timezone = $this->scopeConfig->getValue(
                $this->localeDate->getDefaultTimezonePath(),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $locale = $this->scopeConfig->getValue(
                $this->localeResolver->getDefaultLocalePath(),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $locale = new \Zend_Locale($locale);

            $dateObj = new \Magento\Framework\Stdlib\DateTime\Date(null, null, $locale);
            $dateObj->setTimezone($timezone);
            $this->dates[$storeId] = [$dateObj, $locale->getTranslation(null, 'date', $locale)];
        }

        if (!$this->dateTime->isEmptyDate($date)) {
            list($dateObj, $format) = $this->dates[$storeId];
            $dateObj->setDate($date, \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);

            return $dateObj->toString($format);
        }

        return null;
    }
}
