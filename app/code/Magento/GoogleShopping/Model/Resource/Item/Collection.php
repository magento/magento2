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
namespace Magento\GoogleShopping\Model\Resource\Item;

/**
 * Google Content items collection
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Resource helper
     *
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Eav\Model\Config $config
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Eav\Model\Config $config,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_resourceHelper = $resourceHelper;
        $this->_eavConfig = $config;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\GoogleShopping\Model\Item', 'Magento\GoogleShopping\Model\Resource\Item');
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinTables();
        return $this;
    }

    /**
     * Filter collection by specified store ids
     *
     * @param array|int $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }

    /**
     * Filter collection by specified product id
     *
     * @param int $productId
     * @return $this
     */
    public function addProductFilterId($productId)
    {
        $this->getSelect()->where('main_table.product_id=?', $productId);
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * @param string $field
     * @param null|string|array $condition
     * @return $this
     * @see self::_getConditionSql for $condition
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'name') {
            $conditionSql = $this->_getConditionSql(
                $this->getConnection()->getIfNullSql('p.value', 'p_d.value'),
                $condition
            );
            $this->getSelect()->where($conditionSql, null, \Magento\Framework\DB\Select::TYPE_CONDITION);
            return $this;
        } else {
            return parent::addFieldToFilter($field, $condition);
        }
    }

    /**
     * Join product and type data
     *
     * @return $this
     */
    protected function _joinTables()
    {
        $entityType = $this->_eavConfig->getEntityType('catalog_product');
        $attribute = $this->_eavConfig->getAttribute($entityType->getEntityTypeId(), 'name');

        $joinConditionDefault = sprintf(
            "p_d.attribute_id=%d AND p_d.store_id='0' AND main_table.product_id=p_d.entity_id",
            $attribute->getAttributeId()
        );
        $joinCondition = sprintf(
            "p.attribute_id=%d AND p.store_id=main_table.store_id AND main_table.product_id=p.entity_id",
            $attribute->getAttributeId()
        );

        $this->getSelect()->joinLeft(
            array('p_d' => $attribute->getBackend()->getTable()),
            $joinConditionDefault,
            array()
        );

        $this->getSelect()->joinLeft(
            array('p' => $attribute->getBackend()->getTable()),
            $joinCondition,
            array('name' => $this->getConnection()->getIfNullSql('p.value', 'p_d.value'))
        );

        $this->getSelect()->joinLeft(
            array('types' => $this->getTable('googleshopping_types')),
            'main_table.type_id=types.type_id'
        );
        $this->_resourceHelper->prepareColumnsList($this->getSelect());
        // avoid column name collision

        return $this;
    }
}
