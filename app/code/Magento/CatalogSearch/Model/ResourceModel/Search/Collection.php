<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Search;

/**
 * Search collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection implements
    \Magento\Search\Model\SearchCollectionInterface
{
    /**
     * @var array
     */
    private $indexUsageEnforcements;

    /**
     * Attribute collection
     *
     * @var array
     */
    protected $_attributesCollection;

    /**
     * Search query
     *
     * @var string
     */
    protected $_searchQuery;

    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param array $indexUsageEnforcements
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        array $indexUsageEnforcements = []
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->indexUsageEnforcements = $indexUsageEnforcements;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->_searchQuery = $query;
        $this->addFieldToFilter(
            $this->getEntity()->getLinkField(),
            ['in' => new \Zend_Db_Expr($this->_getSearchEntityIdsSql($query))]
        );
        return $this;
    }

    /**
     * Add backend search query filter (search by all stores)
     *
     * @param string $query
     * @return $this
     */
    public function addBackendSearchFilter($query)
    {
        $this->_searchQuery = $query;
        $this->addFieldToFilter(
            $this->getEntity()->getLinkField(),
            ['in' => new \Zend_Db_Expr($this->_getSearchEntityIdsSql($query, false))]
        );
        return $this;
    }

    /**
     * Retrieve collection of all attributes
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getAttributesCollection()
    {
        if (!$this->_attributesCollection) {
            $this->_attributesCollection = $this->_attributeCollectionFactory->create()->load();

            foreach ($this->_attributesCollection as $attribute) {
                $attribute->setEntity($this->getEntity());
            }
        }
        return $this->_attributesCollection;
    }

    /**
     * Check attribute is Text and is Searchable
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @return boolean
     */
    protected function _isAttributeTextAndSearchable($attribute)
    {
        if ($attribute->getIsSearchable() && !in_array(
            $attribute->getFrontendInput(),
            ['select', 'multiselect']
        ) && (in_array(
            $attribute->getBackendType(),
            ['varchar', 'text']
        ) || $attribute->getBackendType() == 'static')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check attributes has options and searchable
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @return boolean
     */
    protected function _hasAttributeOptionsAndSearchable($attribute)
    {
        if ($attribute->getIsSearchable() && in_array($attribute->getFrontendInput(), ['select', 'multiselect'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Prepare table names for the index enforcements
     *
     * @return array
     */
    private function prepareIndexEnforcements() : array
    {
        $result = [];
        foreach ($this->indexUsageEnforcements as $table => $index) {
            $table = $this->getTable($table);
            if ($this->isIndexExists($table, $index)) {
                $result[$table] = $index;
            }
        }
        return $result;
    }

    /**
     * Check if index exists in the table
     *
     * @param string $table
     * @param string $index
     * @return bool
     */
    private function isIndexExists(string $table, string $index) : bool
    {
        return array_key_exists($index, $this->_conn->getIndexList($table));
    }

    /**
     * Retrieve SQL for search entities
     *
     * @param mixed $query
     * @param bool $searchOnlyInCurrentStore Search only in current store or in all stores
     * @return string
     */
    protected function _getSearchEntityIdsSql($query, $searchOnlyInCurrentStore = true)
    {
        $tables = [];
        $selects = [];
        $preparedIndexEnforcements = $this->prepareIndexEnforcements();

        $likeOptions = ['position' => 'any'];

        $linkField = $this->getEntity()->getLinkField();

        /**
         * Collect tables and attribute ids of attributes with string values
         */
        foreach ($this->_getAttributesCollection() as $attribute) {
            /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $attributeCode = $attribute->getAttributeCode();
            if ($this->_isAttributeTextAndSearchable($attribute)) {
                $table = $attribute->getBackendTable();
                if (!isset($tables[$table]) && $attribute->getBackendType() != 'static') {
                    $tables[$table] = [];
                }

                if ($attribute->getBackendType() == 'static') {
                    $selects[] = $this->getConnection()->select()->from(
                        $table,
                        $linkField
                    )->where(
                        $this->_resourceHelper->getCILike($attributeCode, $this->_searchQuery, $likeOptions)
                    );
                } else {
                    $tables[$table][] = $attribute->getId();
                }
            }
        }

        if ($searchOnlyInCurrentStore) {
            $joinCondition = $this->getConnection()->quoteInto(
                "t1.{$linkField} = t2.{$linkField} AND t1.attribute_id = t2.attribute_id AND t2.store_id = ?",
                $this->getStoreId()
            );
        } else {
            $joinCondition = "t1.{$linkField} = t2.{$linkField} AND t1.attribute_id = t2.attribute_id";
        }

        $ifValueId = $this->getConnection()->getIfNullSql('t2.value', 't1.value');
        foreach ($tables as $table => $attributeIds) {
            if (!empty($preparedIndexEnforcements[$table])) {
                $condition1 = $this->_conn->quoteInto(
                    '`t1`.`attribute_id` IN (?)',
                    $attributeIds,
                    \Zend_Db::INT_TYPE
                );
                $condition2 = '`t1`.`store_id` = 0';
                $quotedField = $this->_conn->quoteIdentifier($ifValueId);
                $condition3 = $this->_conn->quoteInto(
                    $quotedField . ' LIKE ?',
                    $this->_resourceHelper->addLikeEscape($this->_searchQuery, $likeOptions)
                );

                //force index statement not implemented in framework
                // phpcs:ignore Magento2.SQL.RawQuery
                $select = sprintf(
                    'SELECT `t1`.`%s` FROM `%s` AS `t1` FORCE INDEX(%s)
                        LEFT JOIN `%s` AS `t2` FORCE INDEX(%s)
                        ON %s WHERE %s AND %s AND (%s)',
                    $linkField,
                    $table,
                    $preparedIndexEnforcements[$table],
                    $table,
                    $preparedIndexEnforcements[$table],
                    $joinCondition,
                    $condition1,
                    $condition2,
                    $condition3
                );
            } else {
                $select = $this->getConnection()->select();
                $select->from(
                    ['t1' => $table],
                    $linkField
                )->joinLeft(
                    ['t2' => $table],
                    $joinCondition,
                    []
                )->where(
                    't1.attribute_id IN (?)',
                    $attributeIds,
                    \Zend_Db::INT_TYPE
                )->where(
                    't1.store_id = ?',
                    0
                )->where(
                    $this->_resourceHelper->getCILike($ifValueId, $this->_searchQuery, $likeOptions)
                );
            }
            $selects[] = $select;
        }

        $sql = $this->_getSearchInOptionSql($query);
        if ($sql) {
            // phpcs:ignore Magento2.SQL.RawQuery
            $selects[] = "SELECT * FROM ({$sql}) AS inoptionsql"; // inherent unions may be inside
        }

        $sql = $this->getConnection()->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);
        return $sql;
    }

    /**
     * Retrieve SQL for search entities by option
     *
     * @param mixed $query
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getSearchInOptionSql($query)
    {
        $attributeIds = [];
        $attributeTables = [];
        $storeId = (int)$this->getStoreId();

        /**
         * Collect attributes with options
         */
        foreach ($this->_getAttributesCollection() as $attribute) {
            if ($this->_hasAttributeOptionsAndSearchable($attribute)) {
                $attributeTables[$attribute->getFrontendInput()] = $attribute->getBackend()->getTable();
                $attributeIds[] = $attribute->getId();
            }
        }
        if (empty($attributeIds)) {
            return false;
        }

        $optionTable = $this->_resource->getTableName('eav_attribute_option');
        $optionValueTable = $this->_resource->getTableName('eav_attribute_option_value');
        $attributesTable = $this->_resource->getTableName('eav_attribute');

        /**
         * Select option Ids
         */
        $ifStoreId = $this->getConnection()->getIfNullSql('s.store_id', 'd.store_id');
        $ifValue = $this->getConnection()->getCheckSql('s.value_id > 0', 's.value', 'd.value');
        $select = $this->getConnection()->select()->from(
            ['d' => $optionValueTable],
            ['option_id', 'o.attribute_id', 'store_id' => $ifStoreId, 'a.frontend_input']
        )->joinLeft(
            ['s' => $optionValueTable],
            $this->getConnection()->quoteInto('s.option_id = d.option_id AND s.store_id=?', $storeId),
            []
        )->join(
            ['o' => $optionTable],
            'o.option_id=d.option_id',
            []
        )->join(
            ['a' => $attributesTable],
            'o.attribute_id=a.attribute_id',
            []
        )->where(
            'd.store_id=0'
        )->where(
            'o.attribute_id IN (?)',
            $attributeIds,
            \Zend_Db::INT_TYPE
        )->where(
            $this->_resourceHelper->getCILike($ifValue, $this->_searchQuery, ['position' => 'any'])
        );

        $options = $this->getConnection()->fetchAll($select);
        if (empty($options)) {
            return false;
        }

        // build selects of entity ids for specified options ids by frontend input
        $selects = [];
        foreach (['select' => 'eq', 'multiselect' => 'finset'] as $frontendInput => $condition) {
            if (isset($attributeTables[$frontendInput])) {
                $where = [];
                foreach ($options as $option) {
                    if ($frontendInput === $option['frontend_input']) {
                        $findSet = $this->getConnection()->prepareSqlCondition(
                            'value',
                            [$condition => $option['option_id']]
                        );
                        $whereCond = "(attribute_id=%d AND store_id=%d AND {$findSet})";
                        $where[] = sprintf($whereCond, $option['attribute_id'], $option['store_id']);
                    }
                }
                if ($where) {
                    $selects[$frontendInput] = (string)$this->getConnection()->select()->from(
                        $attributeTables[$frontendInput],
                        $this->getEntity()->getLinkField()
                    )->where(
                        implode(' OR ', $where)
                    );
                }
            }
        }

        $sql = $this->getConnection()->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);
        return (string)$sql;
    }
}
