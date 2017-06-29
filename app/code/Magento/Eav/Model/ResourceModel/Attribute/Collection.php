<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Attribute;

use Magento\Store\Model\Website;

/**
 * EAV additional attribute resource collection (Using Forms)
 *
 * @api
 */
abstract class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
{
    /**
     * code of password hash in customer's EAV tables
     */
    const EAV_CODE_PASSWORD_HASH = 'password_hash';

    /**
     * Current website scope instance
     *
     * @var Website
     */
    protected $_website;

    /**
     * Columns in main table
     *
     * @var array
     */
    protected $mainColumns;

    /**
     * Columns in additional attribute table
     *
     * @var array
     */
    protected $extraColumns;

    /**
     * Attribute Entity Type Filter
     *
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_entityType;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $connection, $resource);
    }

    /**
     * Default attribute entity type code
     *
     * @return string
     */
    abstract protected function _getEntityTypeCode();

    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     */
    abstract protected function _getEavWebsiteTable();

    /**
     * Get default attribute entity type code
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEntityTypeCode()
    {
        return $this->_getEntityTypeCode();
    }

    /**
     * Return eav entity type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        if ($this->_entityType === null) {
            $this->_entityType = $this->eavConfig->getEntityType($this->_getEntityTypeCode());
        }
        return $this->_entityType;
    }

    /**
     * Set Website scope
     *
     * @param Website|int $website
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->_website = $this->_storeManager->getWebsite($website);
        $this->addBindParam('scope_website_id', $this->_website->getId());
        return $this;
    }

    /**
     * Return current website scope instance
     *
     * @return Website
     */
    public function getWebsite()
    {
        if ($this->_website === null) {
            $this->_website = $this->_storeManager->getStore()->getWebsite();
        }
        return $this->_website;
    }

    /**
     *  Returns array with columns in main table
     *
     * @return array
     */
    public function getMainColumns()
    {
        if (!$this->mainColumns) {
            $this->mainColumns = [];
            $mainDescribe = $this->getConnection()
                ->describeTable($this->getResource()->getMainTable());

            foreach (array_keys($mainDescribe) as $columnName) {
                $this->mainColumns[$columnName] = $columnName;
            }
        }

        return $this->mainColumns;
    }

    /**
     *  Returns array with columns from additional table
     *
     * @return array
     */
    public function getExtraColumns()
    {
        if (!$this->extraColumns) {
            $extraTable = $this->getEntityType()->getAdditionalAttributeTable();
            $mainColumns = $this->getMainColumns();

            $this->extraColumns = [];
            $extraDescribe = $this->getConnection()
                ->describeTable($this->getTable($extraTable));

            foreach (array_keys($extraDescribe) as $columnName) {
                if (isset($mainColumns[$columnName])) {
                    continue;
                }
                $this->extraColumns[$columnName] = $columnName;
            }
        }

        return $this->extraColumns;
    }

    /**
     * Initialize collection select
     *
     * @return $this
     *
     */
    protected function _initSelect()
    {
        $select = $this->getSelect();
        $entityType = $this->getEntityType();
        $mainColumns = $this->getMainColumns();

        $select->from(['main_table' => $this->getResource()->getMainTable()], $mainColumns);

        $this->addBindParam('mt_entity_type_id', (int)$entityType->getId());
        $select->join(
            ['additional_table' => $this->getTable($entityType->getAdditionalAttributeTable())],
            'additional_table.attribute_id = main_table.attribute_id',
            $this->getExtraColumns()
        )->where(
            'main_table.entity_type_id = :mt_entity_type_id'
        );

        // scope values
        if ($this->_getEavWebsiteTable()) {
            $this->applyScopeValues();
        }

        return $this;
    }

    /**
     * Apply scope values from website tables if implemented
     *
     * @return $this
     */
    public function applyScopeValues()
    {
        $scopeTable = $this->_getEavWebsiteTable();
        $connection = $this->getConnection();

        if ($scopeTable) {
            $mainColumns = $this->getMainColumns();
            $extraColumns = $this->getExtraColumns();
            $scopeDescribe = $connection->describeTable($scopeTable);
            unset($scopeDescribe['attribute_id']);
            $scopeColumns = [];
            foreach (array_keys($scopeDescribe) as $columnName) {
                if ($columnName == 'website_id') {
                    $scopeColumns['scope_website_id'] = $columnName;
                } elseif (isset($mainColumns[$columnName]) || isset($extraColumns[$columnName])) {
                    $tableName = isset($mainColumns[$columnName])? 'main_table' : 'additional_table';
                    $alias = 'scope_' . $columnName;
                    $condition = $tableName . '.' . $columnName . ' IS NULL';
                    $true = 'scope_table.' . $columnName;
                    $false = $tableName . '.' . $columnName;
                    $expression = $connection->getCheckSql($condition, $true, $false);
                    $this->addFilterToMap($columnName, $expression);
                    $scopeColumns[$alias] = $columnName;
                }
            }

            $this->getSelect()->joinLeft(
                ['scope_table' => $scopeTable],
                'scope_table.attribute_id = main_table.attribute_id AND scope_table.website_id = :scope_website_id',
                $scopeColumns
            );
            $websiteId = $this->getWebsite() ? (int)$this->getWebsite()->getId() : 0;
            $this->addBindParam('scope_website_id', $websiteId);
        }

        return $this;
    }

    /**
     * Specify attribute entity type filter.
     * Entity type is defined.
     *
     * @param  int $type
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function setEntityTypeFilter($type)
    {
        return $this;
    }

    /**
     * Specify filter by "is_visible" field
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function addVisibleFilter()
    {
        return $this->addFieldToFilter('is_visible', 1);
    }

    /**
     * Exclude system hidden attributes
     *
     * @return $this
     */
    public function addSystemHiddenFilter()
    {
        $connection = $this->getConnection();
        $expression = $connection->getCheckSql(
            'additional_table.is_system = 1 AND additional_table.is_visible = 0',
            '1',
            '0'
        );
        $this->getSelect()->where($connection->quoteInto($expression . ' = ?', 0));
        return $this;
    }

    /**
     * Exclude system hidden attributes but include password hash
     *
     * @return $this
     */
    public function addSystemHiddenFilterWithPasswordHash()
    {
        $connection = $this->getConnection();
        $expression = $connection->getCheckSql(
            $connection->quoteInto(
                'additional_table.is_system = 1 AND additional_table.is_visible = 0 AND main_table.attribute_code != ?',
                self::EAV_CODE_PASSWORD_HASH
            ),
            '1',
            '0'
        );
        $this->getSelect()->where($connection->quoteInto($expression . ' = ?', 0));
        return $this;
    }

    /**
     * Add exclude hidden frontend input attribute filter to collection
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function addExcludeHiddenFrontendFilter()
    {
        return $this->addFieldToFilter('main_table.frontend_input', ['neq' => 'hidden']);
    }
}
