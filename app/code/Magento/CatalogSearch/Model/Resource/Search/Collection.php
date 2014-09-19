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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Model\Resource\Search;

/**
 * Search collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Catalog\Model\Resource\Product\Collection
{
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
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Zend_Db_Adapter_Abstract $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        $connection = null
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
            $connection
        );
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
        $this->addFieldToFilter('entity_id', array('in' => new \Zend_Db_Expr($this->_getSearchEntityIdsSql($query))));
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
            'entity_id',
            array('in' => new \Zend_Db_Expr($this->_getSearchEntityIdsSql($query, false)))
        );
        return $this;
    }

    /**
     * Retrieve collection of all attributes
     *
     * @return \Magento\Framework\Data\Collection\Db
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
            array('select', 'multiselect')
        ) && (in_array(
            $attribute->getBackendType(),
            array('varchar', 'text')
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
        if ($attribute->getIsSearchable() && in_array($attribute->getFrontendInput(), array('select', 'multiselect'))
        ) {
            return true;
        }

        return false;
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
        $tables = array();
        $selects = array();

        $likeOptions = array('position' => 'any');

        /**
         * Collect tables and attribute ids of attributes with string values
         */
        foreach ($this->_getAttributesCollection() as $attribute) {
            /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $attributeCode = $attribute->getAttributeCode();
            if ($this->_isAttributeTextAndSearchable($attribute)) {
                $table = $attribute->getBackendTable();
                if (!isset($tables[$table]) && $attribute->getBackendType() != 'static') {
                    $tables[$table] = array();
                }

                if ($attribute->getBackendType() == 'static') {
                    $selects[] = $this->getConnection()->select()->from(
                        $table,
                        'entity_id'
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
                't1.entity_id = t2.entity_id AND t1.attribute_id = t2.attribute_id AND t2.store_id = ?',
                $this->getStoreId()
            );
        } else {
            $joinCondition = 't1.entity_id = t2.entity_id AND t1.attribute_id = t2.attribute_id';
        }

        $ifValueId = $this->getConnection()->getIfNullSql('t2.value', 't1.value');
        foreach ($tables as $table => $attributeIds) {
            $selects[] = $this->getConnection()->select()->from(
                array('t1' => $table),
                'entity_id'
            )->joinLeft(
                array('t2' => $table),
                $joinCondition,
                array()
            )->where(
                't1.attribute_id IN (?)',
                $attributeIds
            )->where(
                't1.store_id = ?',
                0
            )->where(
                $this->_resourceHelper->getCILike($ifValueId, $this->_searchQuery, $likeOptions)
            );
        }

        $sql = $this->_getSearchInOptionSql($query);
        if ($sql) {
            $selects[] = "SELECT * FROM ({$sql}) AS inoptionsql"; // inherent unions may be inside
        }

        $sql = $this->getConnection()->select()->union($selects, \Zend_Db_Select::SQL_UNION_ALL);
        return $sql;
    }

    /**
     * Retrieve SQL for search entities by option
     *
     * @param mixed $query
     * @return string
     */
    protected function _getSearchInOptionSql($query)
    {
        $attributeIds = array();
        $attributeTables = array();
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
            array('d' => $optionValueTable),
            array('option_id', 'o.attribute_id', 'store_id' => $ifStoreId, 'a.frontend_input')
        )->joinLeft(
            array('s' => $optionValueTable),
            $this->getConnection()->quoteInto('s.option_id = d.option_id AND s.store_id=?', $storeId),
            array()
        )->join(
            array('o' => $optionTable),
            'o.option_id=d.option_id',
            array()
        )->join(
            array('a' => $attributesTable),
            'o.attribute_id=a.attribute_id',
            array()
        )->where(
            'd.store_id=0'
        )->where(
            'o.attribute_id IN (?)',
            $attributeIds
        )->where(
            $this->_resourceHelper->getCILike($ifValue, $this->_searchQuery, array('position' => 'any'))
        );

        $options = $this->getConnection()->fetchAll($select);
        if (empty($options)) {
            return false;
        }

        // build selects of entity ids for specified options ids by frontend input
        $selects = array();
        foreach (array('select' => 'eq', 'multiselect' => 'finset') as $frontendInput => $condition) {
            if (isset($attributeTables[$frontendInput])) {
                $where = array();
                foreach ($options as $option) {
                    if ($frontendInput === $option['frontend_input']) {
                        $findSet = $this->getConnection()->prepareSqlCondition(
                            'value',
                            array($condition => $option['option_id'])
                        );
                        $whereCond = "(attribute_id=%d AND store_id=%d AND {$findSet})";
                        $where[] = sprintf($whereCond, $option['attribute_id'], $option['store_id']);
                    }
                }
                if ($where) {
                    $selects[$frontendInput] = (string)$this->getConnection()->select()->from(
                        $attributeTables[$frontendInput],
                        'entity_id'
                    )->where(
                        implode(' OR ', $where)
                    );
                }
            }
        }

        $sql = $this->getConnection()->select()->union($selects, \Zend_Db_Select::SQL_UNION_ALL);
        return $sql;
    }
}
