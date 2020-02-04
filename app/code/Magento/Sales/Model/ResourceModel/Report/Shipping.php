<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report;

/**
 * Shipping report resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipping extends AbstractReport
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
     * Aggregate Shipping data
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_aggregateByOrderCreatedAt($from, $to);
        $this->_aggregateByShippingCreatedAt($from, $to);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_SHIPPING_FLAG_CODE);
        return $this;
    }

    /**
     * Aggregate shipping report by order create_at as period
     *
     * @param string|null $from
     * @param string|null $to
     * @return $this
     * @throws \Exception
     */
    protected function _aggregateByOrderCreatedAt($from, $to)
    {
        $table = $this->getTable('sales_shipping_aggregated_order');
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
            $shippingCanceled = $connection->getIfNullSql('base_shipping_canceled', 0);
            $shippingRefunded = $connection->getIfNullSql('base_shipping_refunded', 0);
            $columns = [
                'period' => $periodExpr,
                'store_id' => 'store_id',
                'order_status' => 'status',
                'shipping_description' => 'shipping_description',
                'orders_count' => new \Zend_Db_Expr('COUNT(entity_id)'),
                'total_shipping' => new \Zend_Db_Expr(
                    "SUM((base_shipping_amount - {$shippingCanceled}) * base_to_global_rate)"
                ),
                'total_shipping_actual' => new \Zend_Db_Expr(
                    "SUM((base_shipping_invoiced - {$shippingRefunded}) * base_to_global_rate)"
                ),
            ];

            $select = $connection->select();
            $select->from(
                $sourceTable,
                $columns
            )->where(
                'state NOT IN (?)',
                [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
            )->where(
                'is_virtual = 0'
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group([$periodExpr, 'store_id', 'status', 'shipping_description']);
            $select->having('orders_count > 0');
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $select->reset();

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                'order_status' => 'order_status',
                'shipping_description' => 'shipping_description',
                'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
                'total_shipping' => new \Zend_Db_Expr('SUM(total_shipping)'),
                'total_shipping_actual' => new \Zend_Db_Expr('SUM(total_shipping_actual)'),
            ];

            $select->from($table, $columns)->where('store_id != ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'order_status', 'shipping_description']);
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();
        return $this;
    }

    /**
     * Aggregate shipping report by shipment create_at as period
     *
     * @param string|null $from
     * @param string|null $to
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _aggregateByShippingCreatedAt($from, $to)
    {
        $table = $this->getTable('sales_shipping_aggregated');
        $sourceTable = $this->getTable('sales_invoice');
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
            $shippingCanceled = $connection->getIfNullSql('order_table.base_shipping_canceled', 0);
            $shippingRefunded = $connection->getIfNullSql('order_table.base_shipping_refunded', 0);
            $columns = [
                'period' => $periodExpr,
                'store_id' => 'order_table.store_id',
                'order_status' => 'order_table.status',
                'shipping_description' => 'order_table.shipping_description',
                'orders_count' => new \Zend_Db_Expr('COUNT(order_table.entity_id)'),
                'total_shipping' => new \Zend_Db_Expr(
                    'SUM((order_table.base_shipping_amount - ' .
                    "{$shippingCanceled}) * order_table.base_to_global_rate)"
                ),
                'total_shipping_actual' => new \Zend_Db_Expr(
                    'SUM((order_table.base_shipping_invoiced - ' .
                    "{$shippingRefunded}) * order_table.base_to_global_rate)"
                ),
            ];

            $select = $connection->select();
            $select->from(
                ['source_table' => $sourceTable],
                $columns
            )->joinInner(
                ['order_table' => $orderTable],
                $connection->quoteInto(
                    'source_table.order_id = order_table.entity_id AND order_table.state != ?',
                    \Magento\Sales\Model\Order::STATE_CANCELED
                ),
                []
            )->useStraightJoin();

            $filterSubSelect = $connection->select()->from(
                ['filter_source_table' => $sourceTable],
                'MIN(filter_source_table.entity_id)'
            )->where(
                'filter_source_table.order_id = source_table.order_id'
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->where('source_table.entity_id = (?)', new \Zend_Db_Expr($filterSubSelect));
            unset($filterSubSelect);

            $select->group(
                [$periodExpr, 'order_table.store_id', 'order_table.status', 'order_table.shipping_description']
            );

            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $select->reset();

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                'order_status' => 'order_status',
                'shipping_description' => 'shipping_description',
                'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
                'total_shipping' => new \Zend_Db_Expr('SUM(total_shipping)'),
                'total_shipping_actual' => new \Zend_Db_Expr('SUM(total_shipping_actual)'),
            ];

            $select->from($table, $columns)->where('store_id != ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'order_status', 'shipping_description']);
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
