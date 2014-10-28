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
namespace Magento\Eav\Model\Resource\Form\Attribute;

/**
 * EAV Form Attribute Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Current module pathname
     *
     * @var string
     */
    protected $_moduleName = '';

    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = '';

    /**
     * Current store instance
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * Eav Entity Type instance
     *
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_entityType;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

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
     * Resource initialization
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _construct()
    {
        if (empty($this->_moduleName)) {
            throw new \Magento\Framework\Model\Exception(__('Current module pathname is undefined'));
        }
        if (empty($this->_entityTypeCode)) {
            throw new \Magento\Framework\Model\Exception(__('Current module EAV entity is undefined'));
        }
    }

    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     */
    protected function _getEavWebsiteTable()
    {
        return null;
    }

    /**
     * Set current store to collection
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $this->_storeManager->getStore($store);
        return $this;
    }

    /**
     * Return current store instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->_store === null) {
            $this->_store = $this->_storeManager->getStore();
        }
        return $this->_store;
    }

    /**
     * Set entity type instance to collection
     *
     * @param \Magento\Eav\Model\Entity\Type|string|int $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $this->_eavConfig->getEntityType($entityType);
        return $this;
    }

    /**
     * Return current entity type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        if ($this->_entityType === null) {
            $this->setEntityType($this->_entityTypeCode);
        }
        return $this->_entityType;
    }

    /**
     * Add Form Code filter to collection
     *
     * @param string $code
     * @return $this
     */
    public function addFormCodeFilter($code)
    {
        return $this->addFieldToFilter('main_table.form_code', $code);
    }

    /**
     * Set order by attribute sort order
     *
     * @param string $direction
     * @return $this
     */
    public function setSortOrder($direction = self::SORT_ORDER_ASC)
    {
        $this->setOrder('ea.is_user_defined', self::SORT_ORDER_ASC);
        return $this->setOrder('ca.sort_order', $direction);
    }

    /**
     * Add joins to select
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $select = $this->getSelect();
        $connection = $this->getConnection();
        $entityType = $this->getEntityType();
        $this->setItemObjectClass($entityType->getAttributeModel());

        $eaColumns = array();
        $caColumns = array();
        $saColumns = array();

        $eaDescribe = $connection->describeTable($this->getTable('eav_attribute'));
        unset($eaDescribe['attribute_id']);
        foreach (array_keys($eaDescribe) as $columnName) {
            $eaColumns[$columnName] = $columnName;
        }

        $select->join(
            array('ea' => $this->getTable('eav_attribute')),
            'main_table.attribute_id = ea.attribute_id',
            $eaColumns
        );

        // join additional attribute data table
        $additionalTable = $entityType->getAdditionalAttributeTable();
        if ($additionalTable) {
            $caDescribe = $connection->describeTable($this->getTable($additionalTable));
            unset($caDescribe['attribute_id']);
            foreach (array_keys($caDescribe) as $columnName) {
                $caColumns[$columnName] = $columnName;
            }

            $select->join(
                array('ca' => $this->getTable($additionalTable)),
                'main_table.attribute_id = ca.attribute_id',
                $caColumns
            );
        }

        // add scope values
        if ($this->_getEavWebsiteTable()) {
            $saDescribe = $connection->describeTable($this->_getEavWebsiteTable());
            unset($saDescribe['attribute_id']);
            foreach (array_keys($saDescribe) as $columnName) {
                if ($columnName == 'website_id') {
                    $saColumns['scope_website_id'] = $columnName;
                } else {
                    if (isset($eaColumns[$columnName])) {
                        $code = sprintf('scope_%s', $columnName);
                        $expression = $connection->getCheckSql('sa.%s IS NULL', 'ea.%s', 'sa.%s');
                        $saColumns[$code] = new \Zend_Db_Expr(
                            sprintf($expression, $columnName, $columnName, $columnName)
                        );
                    } elseif (isset($caColumns[$columnName])) {
                        $code = sprintf('scope_%s', $columnName);
                        $expression = $connection->getCheckSql('sa.%s IS NULL', 'ca.%s', 'sa.%s');
                        $saColumns[$code] = new \Zend_Db_Expr(
                            sprintf($expression, $columnName, $columnName, $columnName)
                        );
                    }
                }
            }

            $store = $this->getStore();
            $joinWebsiteExpression = $connection->quoteInto(
                'sa.attribute_id = main_table.attribute_id AND sa.website_id = ?',
                (int)$store->getWebsiteId()
            );
            $select->joinLeft(array('sa' => $this->_getEavWebsiteTable()), $joinWebsiteExpression, $saColumns);
        }


        // add store attribute label
        $storeLabelExpr = $connection->getCheckSql('al.value IS NULL', 'ea.frontend_label', 'al.value');
        $joinExpression = $connection->quoteInto(
            'al.attribute_id = main_table.attribute_id AND al.store_id = ?',
            (int)$store->getId()
        );
        $select->joinLeft(
            array('al' => $this->getTable('eav_attribute_label')),
            $joinExpression,
            array('store_label' => $storeLabelExpr)
        );

        // add entity type filter
        $select->where('ea.entity_type_id = ?', (int)$entityType->getId());

        return parent::_beforeLoad();
    }
}
