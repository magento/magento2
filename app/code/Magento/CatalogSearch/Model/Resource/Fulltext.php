<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Model\Resource;

/**
 * CatalogSearch Fulltext Index resource model
 */
class Fulltext extends \Magento\Model\Resource\Db\AbstractDb
{
    /**
     * Searchable attributes cache
     *
     * @var array
     */
    protected $_searchableAttributes = null;

    /**
     * Index values separator
     *
     * @var string
     */
    protected $_separator = '|';

    /**
     * Array of \Magento\Stdlib\DateTime\DateInterface objects per store
     *
     * @var array
     */
    protected $_dates = array();

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $_productTypes = array();

    /**
     * Product Emulators cache
     *
     * @var array
     */
    protected $_productEmulators = array();

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_productAttributeCollectionFactory;

    /**
     * Catalog product status
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_catalogProductStatus;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Catalog product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $_catalogSearchData;

    /**
     * Core string
     *
     * @var \Magento\Filter\FilterManager
     */
    protected $filter;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CatalogSearch resource helper
     *
     * @var \Magento\CatalogSearch\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\EngineProvider
     */
    protected $_engineProvider;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
     * @param EngineProvider $engineProvider
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Filter\FilterManager $filter
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param Helper $resourceHelper
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Locale\ResolverInterface $localeResolver
     * @param \Magento\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $productAttributeCollectionFactory,
        \Magento\CatalogSearch\Model\Resource\EngineProvider $engineProvider,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Filter\FilterManager $filter,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogSearch\Model\Resource\Helper $resourceHelper,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Locale\ResolverInterface $localeResolver,
        \Magento\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->_catalogProductType = $catalogProductType;
        $this->_eavConfig = $eavConfig;
        $this->_catalogProductStatus = $catalogProductStatus;
        $this->_productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->_eventManager = $eventManager;
        $this->filter = $filter;
        $this->_catalogSearchData = $catalogSearchData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper;
        $this->_engineProvider = $engineProvider;
        $this->dateTime = $dateTime;
        $this->_localeResolver = $localeResolver;
        $this->_localeDate = $localeDate;
        parent::__construct($resource);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_fulltext', 'product_id');
    }

    /**
     * Return options separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Regenerate search index for store(s)
     *
     * @param int|null $storeId
     * @param int|array|null $productIds
     * @return $this
     */
    public function rebuildIndex($storeId = null, $productIds = null)
    {
        if (is_null($storeId)) {
            $storeIds = array_keys($this->_storeManager->getStores());
            foreach ($storeIds as $storeId) {
                $this->_rebuildStoreIndex($storeId, $productIds);
            }
        } else {
            $this->_rebuildStoreIndex($storeId, $productIds);
        }

        return $this;
    }

    /**
     * Regenerate search index for specific store
     *
     * @param int $storeId Store View Id
     * @param int|array $productIds Product Entity Id
     * @return $this
     */
    protected function _rebuildStoreIndex($storeId, $productIds = null)
    {
        $this->cleanIndex($storeId, $productIds);

        // prepare searchable attributes
        $staticFields = array();
        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        $dynamicFields = array(
            'int' => array_keys($this->_getSearchableAttributes('int')),
            'varchar' => array_keys($this->_getSearchableAttributes('varchar')),
            'text' => array_keys($this->_getSearchableAttributes('text')),
            'decimal' => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime' => array_keys($this->_getSearchableAttributes('datetime'))
        );

        // status and visibility filter
        $visibility = $this->_getSearchableAttribute('visibility');
        $status = $this->_getSearchableAttribute('status');
        $statusVals = $this->_catalogProductStatus->getVisibleStatusIds();
        $allowedVisibility = $this->_engineProvider->get()->getAllowedVisibility();

        $lastProductId = 0;
        while (true) {
            $products = $this->_getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);
            if (!$products) {
                break;
            }

            $productAttributes = array();
            $productRelations = array();
            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChildren = $this->_getProductChildIds($productData['entity_id'], $productData['type_id']);
                $productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }

            $productIndexes = array();
            $productAttributes = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);
            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];
                if (!isset(
                    $productAttr[$visibility->getId()]
                ) || !in_array(
                    $productAttr[$visibility->getId()],
                    $allowedVisibility
                )
                ) {
                    continue;
                }
                if (!isset($productAttr[$status->getId()]) || !in_array($productAttr[$status->getId()], $statusVals)) {
                    continue;
                }

                $productIndex = array($productData['entity_id'] => $productAttr);

                $productChildren = $productRelations[$productData['entity_id']];
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        if (isset($productAttributes[$productChildId])) {
                            $productIndex[$productChildId] = $productAttributes[$productChildId];
                        }
                    }
                }

                $index = $this->_prepareProductIndex($productIndex, $productData, $storeId);

                $productIndexes[$productData['entity_id']] = $index;
            }

            $this->_saveProductIndexes($storeId, $productIndexes);
        }

        // Reset only product-specific queries and results.
        $this->resetSearchResults($storeId, $productIds);

        return $this;
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
    protected function _getSearchableProducts(
        $storeId,
        array $staticFields,
        $productIds = null,
        $lastProductId = 0,
        $limit = 100
    ) {
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        $writeAdapter = $this->_getWriteAdapter();

        $select = $writeAdapter->select()->useStraightJoin(
            true
        )->from(
            array('e' => $this->getTable('catalog_product_entity')),
            array_merge(array('entity_id', 'type_id'), $staticFields)
        )->join(
            array('website' => $this->getTable('catalog_product_website')),
            $writeAdapter->quoteInto('website.product_id=e.entity_id AND website.website_id=?', $websiteId),
            array()
        )->join(
            array('stock_status' => $this->getTable('cataloginventory_stock_status')),
            $writeAdapter->quoteInto('stock_status.product_id=e.entity_id AND stock_status.website_id=?', $websiteId),
            array('in_stock' => 'stock_status')
        );

        if (!is_null($productIds)) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $select->where('e.entity_id>?', $lastProductId)->limit($limit)->order('e.entity_id');

        $result = $writeAdapter->fetchAll($select);

        return $result;
    }

    /**
     * Reset search results
     *
     * @param null|int $storeId
     * @param null|array $productIds
     * @return $this
     */
    public function resetSearchResults($storeId = null, $productIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->getTable('catalogsearch_query'), array('is_processed' => 0));

        if ($storeId === null && $productIds === null) {
            // Keeping public interface
            $adapter->update($this->getTable('catalogsearch_query'), array('is_processed' => 0));
            $adapter->delete($this->getTable('catalogsearch_result'));
            $this->_eventManager->dispatch('catalogsearch_reset_search_result');
        } else {
            // Optimized deletion only product-related records
            /** @var $select \Magento\DB\Select */
            $select = $adapter->select()->from(
                array('r' => $this->getTable('catalogsearch_result')),
                null
            )->join(
                array('q' => $this->getTable('catalogsearch_query')),
                'q.query_id=r.query_id',
                array()
            )->join(
                array('res' => $this->getTable('catalogsearch_result')),
                'q.query_id=res.query_id',
                array()
            );
            if (!empty($storeId)) {
                $select->where('q.store_id = ?', $storeId);
            }
            if (!empty($productIds)) {
                $select->where('r.product_id IN(?)', $productIds);
            }
            $query = $select->deleteFromSelect('res');
            $adapter->query($query);

            /** @var $select \Magento\DB\Select */
            $select = $adapter->select();
            $subSelect = $adapter->select()->from(array('res' => $this->getTable('catalogsearch_result')), null);
            $select->exists($subSelect, 'res.query_id=' . $this->getTable('catalogsearch_query') . '.query_id', false);

            $adapter->update(
                $this->getTable('catalogsearch_query'),
                array('is_processed' => 0),
                $select->getPart(\Zend_Db_Select::WHERE)
            );
        }

        return $this;
    }

    /**
     * Delete search index data for store
     *
     * @param int $storeId Store View Id
     * @param int $productId Product Entity Id
     * @return $this
     */
    public function cleanIndex($storeId = null, $productId = null)
    {
        if ($this->_engineProvider->get()) {
            $this->_engineProvider->get()->cleanIndex($storeId, $productId);
        }

        return $this;
    }

    /**
     * Prepare results for query
     *
     * @param \Magento\CatalogSearch\Model\Fulltext $object
     * @param string $queryText
     * @param \Magento\CatalogSearch\Model\Query $query
     * @return $this
     */
    public function prepareResult($object, $queryText, $query)
    {
        $adapter = $this->_getWriteAdapter();
        if (!$query->getIsProcessed()) {
            $searchType = $object->getSearchType($query->getStoreId());

            $bind = array();
            $like = array();
            $likeCond = '';
            if ($searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_LIKE ||
                $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE
            ) {
                $words = $this->filter->splitWords(
                    $queryText,
                    array('uniqueOnly' => true, 'wordsQty' => $query->getMaxQueryWords())
                );
                foreach ($words as $word) {
                    $like[] = $this->_resourceHelper->getCILike('s.data_index', $word, array('position' => 'any'));
                }
                if ($like) {
                    $likeCond = '(' . join(' OR ', $like) . ')';
                }
            }
            $mainTableAlias = 's';
            $fields = array('query_id' => new \Zend_Db_Expr($query->getId()), 'product_id');
            $select = $adapter->select()->from(
                array($mainTableAlias => $this->getMainTable()),
                $fields
            )->joinInner(
                array('e' => $this->getTable('catalog_product_entity')),
                'e.entity_id = s.product_id',
                array()
            )->where(
                $mainTableAlias . '.store_id = ?',
                (int)$query->getStoreId()
            );

            $where = '';
            if ($searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_FULLTEXT ||
                $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE
            ) {
                $preparedTerms = $this->_resourceHelper->prepareTerms($queryText, $query->getMaxQueryWords());
                $bind[':query'] = implode(' ', $preparedTerms[0]);
                $where = $this->_resourceHelper->chooseFulltext($this->getMainTable(), $mainTableAlias, $select);
            }

            if ($likeCond != '' && $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE) {
                $where .= ($where ? ' OR ' : '') . $likeCond;
            } elseif ($likeCond != '' && $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_LIKE) {
                $select->columns(array('relevance' => new \Zend_Db_Expr(0)));
                $where = $likeCond;
            }

            if ($where != '') {
                $select->where($where);
            }

            $sql = $adapter->insertFromSelect(
                $select,
                $this->getTable('catalogsearch_result'),
                array(),
                \Magento\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $adapter->query($sql, $bind);

            $query->setIsProcessed(1);
        }

        return $this;
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return \Magento\Eav\Model\Config
     */
    public function getEavConfig()
    {
        return $this->_eavConfig;
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     * @return array
     */
    protected function _getSearchableAttributes($backendType = null)
    {
        if (null === $this->_searchableAttributes) {
            $this->_searchableAttributes = array();

            $productAttributes = $this->_productAttributeCollectionFactory->create();

            if ($this->_engineProvider->get() && $this->_engineProvider->get()->allowAdvancedIndex()) {
                $productAttributes->addToIndexFilter(true);
            } else {
                $productAttributes->addSearchableAttributeFilter();
            }
            $attributes = $productAttributes->getItems();

            $this->_eventManager->dispatch(
                'catelogsearch_searchable_attributes_load_after',
                array('engine' => $this->_engineProvider->get(), 'attributes' => $attributes)
            );

            $entity = $this->getEavConfig()->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->_searchableAttributes = $attributes;
        }

        if (!is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchableAttributes as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
        }

        return $this->_searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    protected function _getSearchableAttribute($attribute)
    {
        $attributes = $this->_getSearchableAttributes();
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
    protected function _unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->_getReadAdapter()->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
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
    protected function _getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        $result = array();
        $selects = array();
        $adapter = $this->_getWriteAdapter();
        $ifStoreValue = $adapter->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable('catalog_product_entity_' . $backendType);
                $selects[] = $adapter->select()->from(
                    array('t_default' => $tableName),
                    array('entity_id', 'attribute_id')
                )->joinLeft(
                    array('t_store' => $tableName),
                    $adapter->quoteInto(
                        't_default.entity_id=t_store.entity_id' .
                        ' AND t_default.attribute_id=t_store.attribute_id' .
                        ' AND t_store.store_id=?',
                        $storeId
                    ),
                    array('value' => $this->_unifyField($ifStoreValue, $backendType))
                )->where(
                    't_default.store_id=?',
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
    protected function _getProductTypeInstance($typeId)
    {
        if (!isset($this->_productTypes[$typeId])) {
            $productEmulator = $this->_getProductEmulator($typeId);

            $this->_productTypes[$typeId] = $this->_catalogProductType->factory($productEmulator);
        }
        return $this->_productTypes[$typeId];
    }

    /**
     * Return all product children ids
     *
     * @param int $productId Product Entity Id
     * @param string $typeId Super Product Link Type
     * @return array|null
     */
    protected function _getProductChildIds($productId, $typeId)
    {
        $typeInstance = $this->_getProductTypeInstance($typeId);
        $relation = $typeInstance->isComposite(
            $this->_getProductEmulator($typeId)
        ) ? $typeInstance->getRelationInfo() : false;

        if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
            $select = $this->_getReadAdapter()->select()->from(
                array('main' => $this->getTable($relation->getTable())),
                array($relation->getChildFieldName())
            )->where(
                $relation->getParentFieldName() . '=?',
                $productId
            );
            if (!is_null($relation->getWhere())) {
                $select->where($relation->getWhere());
            }
            return $this->_getReadAdapter()->fetchCol($select);
        }

        return null;
    }

    /**
     * Retrieve Product Emulator (Magento Object)
     *
     * @param string $typeId
     * @return \Magento\Object
     */
    protected function _getProductEmulator($typeId)
    {
        if (!isset($this->_productEmulators[$typeId])) {
            $productEmulator = new \Magento\Object();
            $productEmulator->setIdFieldName('entity_id')->setTypeId($typeId);
            $this->_productEmulators[$typeId] = $productEmulator;
        }
        return $this->_productEmulators[$typeId];
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return string
     */
    protected function _prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                $value = $this->_getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    if (isset($index[$attributeCode])) {
                        if (!is_array($index[$attributeCode])) {
                            $index[$attributeCode] = array($index[$attributeCode]);
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
                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!is_null($value) && $value !== false) {
                    $attributeCode = $this->_getSearchableAttribute($attributeId)->getAttributeCode();

                    if (isset($index[$attributeCode])) {
                        $index[$attributeCode][$entityId] = $value;
                    } else {
                        $index[$attributeCode] = array($entityId => $value);
                    }
                }
            }
        }

        if (!$this->_engineProvider->get()->allowAdvancedIndex()) {
            $product = $this->_getProductEmulator(
                $productData['type_id']
            )->setId(
                $productData['entity_id']
            )->setStoreId(
                $storeId
            );
            $typeInstance = $this->_getProductTypeInstance($productData['type_id']);
            $data = $typeInstance->getSearchableData($product);
            if ($data) {
                $index['options'] = $data;
            }
        }

        if (isset($productData['in_stock'])) {
            $index['in_stock'] = $productData['in_stock'];
        }

        if ($this->_engineProvider->get()) {
            return $this->_engineProvider->get()->prepareEntityIndex($index, $this->_separator);
        }

        return $this->_catalogSearchData->prepareIndexdata($index, $this->_separator);
    }

    /**
     * Retrieve attribute source value for search
     *
     * @param int $attributeId
     * @param mixed $value
     * @param int $storeId
     * @return mixed
     */
    protected function _getAttributeValue($attributeId, $value, $storeId)
    {
        $attribute = $this->_getSearchableAttribute($attributeId);
        if (!$attribute->getIsSearchable()) {
            if ($this->_engineProvider->get()->allowAdvancedIndex()) {
                if ($attribute->getAttributeCode() == 'visibility') {
                    return $value;
                } elseif (!($attribute->getIsVisibleInAdvancedSearch() ||
                    $attribute->getIsFilterable() ||
                    $attribute->getIsFilterableInSearch() ||
                    $attribute->getUsedForSortBy())
                ) {
                    return null;
                }
            } else {
                return null;
            }
        }

        if ($attribute->usesSource()) {
            if ($this->_engineProvider->get()->allowAdvancedIndex()) {
                return $value;
            }

            $attribute->setStoreId($storeId);
            $value = $attribute->getSource()->getIndexOptionText($value);

            if (is_array($value)) {
                $value = implode($this->_separator, $value);
            } elseif (empty($value)) {
                $inputType = $attribute->getFrontend()->getInputType();
                if ($inputType == 'select' || $inputType == 'multiselect') {
                    return null;
                }
            }
        } elseif ($attribute->getBackendType() == 'datetime') {
            $value = $this->_getStoreDate($storeId, $value);
        } else {
            $inputType = $attribute->getFrontend()->getInputType();
            if ($inputType == 'price') {
                $value = $this->_storeManager->getStore($storeId)->roundPrice($value);
            }
        }

        $value = preg_replace("#\s+#siu", ' ', trim(strip_tags($value)));

        return $value;
    }

    /**
     * Save Product index
     *
     * @param int $productId
     * @param int $storeId
     * @param string $index
     * @return $this
     */
    protected function _saveProductIndex($productId, $storeId, $index)
    {
        if ($this->_engineProvider->get()) {
            $this->_engineProvider->get()->saveEntityIndex($productId, $storeId, $index);
        }

        return $this;
    }

    /**
     * Save Multiply Product indexes
     *
     * @param int $storeId
     * @param array $productIndexes
     * @return $this
     */
    protected function _saveProductIndexes($storeId, $productIndexes)
    {
        if ($this->_engineProvider->get()) {
            $this->_engineProvider->get()->saveEntityIndexes($storeId, $productIndexes);
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
    protected function _getStoreDate($storeId, $date = null)
    {
        if (!isset($this->_dates[$storeId])) {
            $timezone = $this->_coreStoreConfig->getConfig($this->_localeDate->getDefaultTimezonePath(), $storeId);
            $locale = $this->_coreStoreConfig->getConfig($this->_localeResolver->getDefaultLocalePath(), $storeId);
            $locale = new \Zend_Locale($locale);

            $dateObj = new \Magento\Stdlib\DateTime\Date(null, null, $locale);
            $dateObj->setTimezone($timezone);
            $this->_dates[$storeId] = array($dateObj, $locale->getTranslation(null, 'date', $locale));
        }

        if (!$this->dateTime->isEmptyDate($date)) {
            list($dateObj, $format) = $this->_dates[$storeId];
            $dateObj->setDate($date, \Magento\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);

            return $dateObj->toString($format);
        }

        return null;
    }

    // Deprecated methods
    /**
     * Update category products indexes
     *
     * @deprecated after 1.6.2.0
     *
     * @param array $productIds
     * @param array $categoryIds
     * @return $this
     */
    public function updateCategoryIndex($productIds, $categoryIds)
    {
        return $this;
    }
}
