<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax report resource model with aggregation by created at
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\ResourceModel\Report\Tax;

/**
 * Class for tax report resource model with aggregation by created at
 */
class Createdat extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tax_order_aggregated_created', 'id');
    }

    /**
     * Aggregate Tax data by order created at
     *
     * @param mixed $from
     * @param mixed $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByOrder('created_at', $from, $to);
    }

    /**
     * Aggregate Tax data by orders
     *
     * @param string $aggregationField
     * @param mixed $from
     * @param mixed $to
     * @return $this
     * @throws \Exception
     */
    protected function _aggregateByOrder($aggregationField, $from, $to)
    {
        $connection = $this->getConnection();
        $salesAdapter = $this->_resources->getConnection('sales');

        $connection->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales_order'),
                    'created_at',
                    'updated_at',
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect, false, $salesAdapter);
            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    ['e' => $this->getTable('sales_order')],
                    'e.' . $aggregationField,
                    $from,
                    $to,
                    null,
                    $salesAdapter
                )
            );

            $columns = [
                'period' => $periodExpr,
                'store_id' => 'e.store_id',
                'code' => 'tax.code',
                'order_status' => 'e.status',
                'percent' => 'MAX(tax.' . $connection->quoteIdentifier('percent') . ')',
                'orders_count' => 'COUNT(DISTINCT e.entity_id)',
                'tax_base_amount_sum' => 'SUM(tax.base_real_amount * e.base_to_global_rate)',
            ];

            $select = $connection->select()->from(
                ['tax' => $this->getTable('sales_order_tax')],
                $columns
            )->joinInner(
                ['e' => $this->getTable('sales_order')],
                'e.entity_id = tax.order_id',
                []
            )->useStraightJoin()->where(
                'e.state NOT IN (?)',
                [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period', $salesAdapter));
            }

            $select->group([$periodExpr, 'e.store_id', 'code', 'tax.percent', 'e.status']);

            $aggregatedData = $salesAdapter->fetchAll($select);

            if ($aggregatedData) {
                $connection->insertArray($this->getMainTable(), array_keys($columns), $aggregatedData);
            }

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                'code' => 'code',
                'order_status' => 'order_status',
                'percent' => 'MAX(' . $connection->quoteIdentifier('percent') . ')',
                'orders_count' => 'SUM(orders_count)',
                'tax_base_amount_sum' => 'SUM(tax_base_amount_sum)',
            ];

            $select->reset()->from($this->getMainTable(), $columns)->where('store_id <> ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period', $salesAdapter));
            }

            $select->group(['period', 'code', 'percent', 'order_status']);
            $insertQuery = $connection->insertFromSelect($select, $this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $this;
    }
}
