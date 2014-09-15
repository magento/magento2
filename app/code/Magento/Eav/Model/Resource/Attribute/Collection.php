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
namespace Magento\Eav\Model\Resource\Attribute;

use Magento\Store\Model\Website;

/**
 * EAV additional attribute resource collection (Using Forms)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Collection extends \Magento\Eav\Model\Resource\Entity\Attribute\Collection
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
     * Attribute Entity Type Filter
     *
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_entityType;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
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
            $this->_entityType = $this->_eavConfig->getEntityType($this->_getEntityTypeCode());
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
     * Initialize collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $select = $this->getSelect();
        $connection = $this->getConnection();
        $entityType = $this->getEntityType();
        $extraTable = $entityType->getAdditionalAttributeTable();
        $mainDescribe = $this->getConnection()->describeTable($this->getResource()->getMainTable());
        $mainColumns = array();

        foreach (array_keys($mainDescribe) as $columnName) {
            $mainColumns[$columnName] = $columnName;
        }

        $select->from(array('main_table' => $this->getResource()->getMainTable()), $mainColumns);

        // additional attribute data table
        $extraDescribe = $connection->describeTable($this->getTable($extraTable));
        $extraColumns = array();
        foreach (array_keys($extraDescribe) as $columnName) {
            if (isset($mainColumns[$columnName])) {
                continue;
            }
            $extraColumns[$columnName] = $columnName;
        }

        $this->addBindParam('mt_entity_type_id', (int)$entityType->getId());
        $select->join(
            array('additional_table' => $this->getTable($extraTable)),
            'additional_table.attribute_id = main_table.attribute_id',
            $extraColumns
        )->where(
            'main_table.entity_type_id = :mt_entity_type_id'
        );

        // scope values

        $scopeDescribe = $connection->describeTable($this->_getEavWebsiteTable());
        unset($scopeDescribe['attribute_id']);
        $scopeColumns = array();
        foreach (array_keys($scopeDescribe) as $columnName) {
            if ($columnName == 'website_id') {
                $scopeColumns['scope_website_id'] = $columnName;
            } else {
                if (isset($mainColumns[$columnName])) {
                    $alias = 'scope_' . $columnName;
                    $condition = 'main_table.' . $columnName . ' IS NULL';
                    $true = 'scope_table.' . $columnName;
                    $false = 'main_table.' . $columnName;
                    $expression = $connection->getCheckSql($condition, $true, $false);
                    $this->addFilterToMap($columnName, $expression);
                    $scopeColumns[$alias] = $columnName;
                } elseif (isset($extraColumns[$columnName])) {
                    $alias = 'scope_' . $columnName;
                    $condition = 'additional_table.' . $columnName . ' IS NULL';
                    $true = 'scope_table.' . $columnName;
                    $false = 'additional_table.' . $columnName;
                    $expression = $connection->getCheckSql($condition, $true, $false);
                    $this->addFilterToMap($columnName, $expression);
                    $scopeColumns[$alias] = $columnName;
                }
            }
        }

        $select->joinLeft(
            array('scope_table' => $this->_getEavWebsiteTable()),
            'scope_table.attribute_id = main_table.attribute_id AND scope_table.website_id = :scope_website_id',
            $scopeColumns
        );
        $websiteId = $this->getWebsite() ? (int)$this->getWebsite()->getId() : 0;
        $this->addBindParam('scope_website_id', $websiteId);

        return $this;
    }

    /**
     * Specify attribute entity type filter.
     * Entity type is defined.
     *
     * @param  int $type
     * @return $this
     */
    public function setEntityTypeFilter($type)
    {
        return $this;
    }

    /**
     * Specify filter by "is_visible" field
     *
     * @return $this
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
     */
    public function addExcludeHiddenFrontendFilter()
    {
        return $this->addFieldToFilter('main_table.frontend_input', array('neq' => 'hidden'));
    }
}
