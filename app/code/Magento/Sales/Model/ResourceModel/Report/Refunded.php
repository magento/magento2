<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report;

/**
 * Refund report resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Refunded extends AbstractReport
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setResource('sales');
    }

    /**
     * Aggregate Refunded data
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_aggregateByOrderCreatedAt($from, $to);
        $this->_aggregateByRefundCreatedAt($from, $to);

        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_REFUNDED_FLAG_CODE);
        return $this;
    }

    /**
     * Aggregate refunded data by order created at as period
     *
     * @param string|null $from
     * @param string|null $to
     * @return $this
     * @throws \Exception
     */
    protected function _aggregateByOrderCreatedAt($from, $to)
    {
        $table = $this->getTable('sales_refunded_aggregated_order');
        $sourceTable = $this->getTable('sales_order');
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $from, $to);
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect);
            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery($sourceTable, 'created_at', $from, $to)
            );
            $columns = [
                'period' => $periodExpr,
                'store_id' => 'store_id',
                'order_status' => 'status',
                'orders_count' => new \Zend_Db_Expr('COUNT(total_refunded)'),
                'refunded' => new \Zend_Db_Expr('SUM(base_total_refunded * base_to_global_rate)'),
                'online_refunded' => new \Zend_Db_Expr('SUM(base_total_online_refunded * base_to_global_rate)'),
                'offline_refunded' => new \Zend_Db_Expr('SUM(base_total_offline_refunded * base_to_global_rate)'),
            ];

            $select = $connection->select();
            $select->from(
                $sourceTable,
                $columns
            )->where(
                'state != ?',
                \Magento\Sales\Model\Order::STATE_CANCELED
            )->where(
                'base_total_refunded > ?',
                0
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group([$periodExpr, 'store_id', 'status']);
            $select->having('orders_count > 0');
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $select->reset();

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr('0'),
                'order_status' => 'order_status',
                'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
                'refunded' => new \Zend_Db_Expr('SUM(refunded)'),
                'online_refunded' => new \Zend_Db_Expr('SUM(online_refunded)'),
                'offline_refunded' => new \Zend_Db_Expr('SUM(offline_refunded)'),
            ];

            $select->from($table, $columns)->where('store_id != ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'order_status']);
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Aggregate refunded data by creditmemo created at as period
     *
     * @param string|null $from
     * @param string|null $to
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _aggregateByRefundCreatedAt($from, $to)
    {
        $table = $this->getTable('sales_refunded_aggregated');
        $sourceTable = $this->getTable('sales_creditmemo');
        $orderTable = $this->getTable('sales_order');
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeRelatedSelect(
                    $sourceTable,
                    $orderTable,
                    ['order_id' => 'entity_id'],
                    'created_at',
                    'updated_at',
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect);
            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    ['source_table' => $sourceTable],
                    'source_table.created_at',
                    $from,
                    $to
                )
            );

            $columns = [
                'period' => $periodExpr,
                'store_id' => 'order_table.store_id',
                'order_status' => 'order_table.status',
                'orders_count' => new \Zend_Db_Expr('COUNT(order_table.entity_id)'),
                'refunded' => new \Zend_Db_Expr(
                    'SUM(order_table.base_total_refunded * order_table.base_to_global_rate)'
                ),
                'online_refunded' => new \Zend_Db_Expr(
                    'SUM(order_table.base_total_online_refunded * order_table.base_to_global_rate)'
                ),
                'offline_refunded' => new \Zend_Db_Expr(
                    'SUM(order_table.base_total_offline_refunded * order_table.base_to_global_rate)'
                ),
            ];

            $select = $connection->select();
            $select->from(
                ['source_table' => $sourceTable],
                $columns
            )->joinInner(
                ['order_table' => $orderTable],
                'source_table.order_id = order_table.entity_id AND ' . $connection->quoteInto(
                    'order_table.state != ?',
                    \Magento\Sales\Model\Order::STATE_CANCELED
                ) . ' AND order_table.base_total_refunded > 0',
                []
            );

            $filterSubSelect = $connection->select();
            $filterSubSelect->from(
                ['filter_source_table' => $sourceTable],
                new \Zend_Db_Expr('MAX(filter_source_table.entity_id)')
            )->where(
                'filter_source_table.order_id = source_table.order_id'
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->where('source_table.entity_id = (?)', new \Zend_Db_Expr($filterSubSelect));
            unset($filterSubSelect);

            $select->group([$periodExpr, 'order_table.store_id', 'order_table.status']);
            $select->having('orders_count > 0');

            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $select->reset();

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr('0'),
                'order_status' => 'order_status',
                'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
                'refunded' => new \Zend_Db_Expr('SUM(refunded)'),
                'online_refunded' => new \Zend_Db_Expr('SUM(online_refunded)'),
                'offline_refunded' => new \Zend_Db_Expr('SUM(offline_refunded)'),
            ];

            $select->from($table, $columns)->where('store_id != ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'order_status']);
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        $connection->commit();
        return $this;
    }
}
