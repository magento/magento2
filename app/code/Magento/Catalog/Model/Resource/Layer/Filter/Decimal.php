<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @param float $range
     * @param int $index
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function applyFilterToCollection(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter, $range, $index)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
        ];

        $collection->getSelect()->join(
            [$tableAlias => $this->getMainTable()],
            implode(' AND ', $conditions),
            []
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
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @return array
     */
    public function getMinMax(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter)
    {
        $select = $this->_getSelect($filter);
        $adapter = $this->_getReadAdapter();

        $select->columns(
            [
                'min_value' => new \Zend_Db_Expr('MIN(decimal_index.value)'),
                'max_value' => new \Zend_Db_Expr('MAX(decimal_index.value)'),
            ]
        );

        $result = $adapter->fetchRow($select);

        return [$result['min_value'], $result['max_value']];
    }

    /**
     * Retrieve clean select with joined index table
     * Joined table has index
     *
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @throws \Magento\Framework\Model\Exception
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
            ['decimal_index' => $this->getMainTable()],
            'e.entity_id = decimal_index.entity_id' . ' AND ' . $this->_getReadAdapter()->quoteInto(
                'decimal_index.attribute_id = ?',
                $attributeId
            ) . ' AND ' . $this->_getReadAdapter()->quoteInto(
                'decimal_index.store_id = ?',
                $storeId
            ),
            []
        );

        return $select;
    }

    /**
     * Retrieve array with products counts per range
     *
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @param int $range
     * @return array
     */
    public function getCount(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter, $range)
    {
        $select = $this->_getSelect($filter);
        $adapter = $this->_getReadAdapter();

        $countExpr = new \Zend_Db_Expr("COUNT(*)");
        $rangeExpr = new \Zend_Db_Expr("FLOOR(decimal_index.value / {$range}) + 1");

        $select->columns(['decimal_range' => $rangeExpr, 'count' => $countExpr]);
        $select->group($rangeExpr);

        return $adapter->fetchPairs($select);
    }
}
