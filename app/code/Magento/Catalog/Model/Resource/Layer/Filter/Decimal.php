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
namespace Magento\Catalog\Model\Resource\Layer\Filter;

/**
 * Catalog Layer Decimal attribute Filter Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Decimal extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize connection and define main table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav_decimal', 'entity_id');
    }

    /**
     * Apply attribute filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Decimal $filter
     * @param float $range
     * @param int $index
     * @return $this
     */
    public function applyFilterToCollection($filter, $range, $index)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId())
        );

        $collection->getSelect()->join(
            array($tableAlias => $this->getMainTable()),
            implode(' AND ', $conditions),
            array()
        );

        $collection->getSelect()->where(
            "{$tableAlias}.value >= ?",
            $range * ($index - 1)
        )->where(
            "{$tableAlias}.value < ?",
            $range * $index
        );

        return $this;
    }

    /**
     * Retrieve array of minimal and maximal values
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Decimal $filter
     * @return array
     */
    public function getMinMax($filter)
    {
        $select = $this->_getSelect($filter);
        $adapter = $this->_getReadAdapter();

        $select->columns(
            array(
                'min_value' => new \Zend_Db_Expr('MIN(decimal_index.value)'),
                'max_value' => new \Zend_Db_Expr('MAX(decimal_index.value)')
            )
        );

        $result = $adapter->fetchRow($select);

        return array($result['min_value'], $result['max_value']);
    }

    /**
     * Retrieve clean select with joined index table
     * Joined table has index
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Decimal $filter
     * @return \Magento\Framework\DB\Select
     */
    protected function _getSelect($filter)
    {
        $collection = $filter->getLayer()->getProductCollection();

        // clone select from collection with filters
        $select = clone $collection->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(\Zend_Db_Select::COLUMNS);
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);

        $attributeId = $filter->getAttributeModel()->getId();
        $storeId = $collection->getStoreId();

        $select->join(
            array('decimal_index' => $this->getMainTable()),
            'e.entity_id = decimal_index.entity_id' . ' AND ' . $this->_getReadAdapter()->quoteInto(
                'decimal_index.attribute_id = ?',
                $attributeId
            ) . ' AND ' . $this->_getReadAdapter()->quoteInto(
                'decimal_index.store_id = ?',
                $storeId
            ),
            array()
        );

        return $select;
    }

    /**
     * Retrieve array with products counts per range
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Decimal $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        $select = $this->_getSelect($filter);
        $adapter = $this->_getReadAdapter();

        $countExpr = new \Zend_Db_Expr("COUNT(*)");
        $rangeExpr = new \Zend_Db_Expr("FLOOR(decimal_index.value / {$range}) + 1");

        $select->columns(array('decimal_range' => $rangeExpr, 'count' => $countExpr));
        $select->group($rangeExpr);

        return $adapter->fetchPairs($select);
    }
}
