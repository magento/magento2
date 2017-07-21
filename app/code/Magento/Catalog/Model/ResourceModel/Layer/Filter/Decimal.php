<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Layer\Filter;

/**
 * Catalog Layer Decimal attribute Filter Resource Model
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Decimal extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function applyFilterToCollection(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter, $range, $index)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->getConnection();
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
        $connection = $this->getConnection();

        $select->columns(
            [
                'min_value' => new \Zend_Db_Expr('MIN(decimal_index.value)'),
                'max_value' => new \Zend_Db_Expr('MAX(decimal_index.value)'),
            ]
        );

        $result = $connection->fetchRow($select);

        return [$result['min_value'], $result['max_value']];
    }

    /**
     * Retrieve clean select with joined index table
     * Joined table has index
     *
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\DB\Select
     */
    protected function _getSelect($filter)
    {
        $collection = $filter->getLayer()->getProductCollection();

        // clone select from collection with filters
        $select = clone $collection->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        $attributeId = $filter->getAttributeModel()->getId();
        $storeId = $collection->getStoreId();

        $select->join(
            ['decimal_index' => $this->getMainTable()],
            'e.entity_id = decimal_index.entity_id' . ' AND ' . $this->getConnection()->quoteInto(
                'decimal_index.attribute_id = ?',
                $attributeId
            ) . ' AND ' . $this->getConnection()->quoteInto(
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
        $connection = $this->getConnection();

        $countExpr = new \Zend_Db_Expr("COUNT(*)");
        $rangeExpr = new \Zend_Db_Expr("FLOOR(decimal_index.value / {$range}) + 1");

        $select->columns(['decimal_range' => $rangeExpr, 'count' => $countExpr]);
        $select->group($rangeExpr);

        return $connection->fetchPairs($select);
    }
}
