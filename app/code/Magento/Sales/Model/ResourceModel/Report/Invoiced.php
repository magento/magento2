<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report;

/**
 * Invoice report resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Invoiced extends AbstractReport
{
    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_setResource('sales');
    }

    /**
     * Aggregate Invoiced data
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @since 2.0.0
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_aggregateByOrderCreatedAt($from, $to);
        $this->_aggregateByInvoiceCreatedAt($from, $to);

        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_INVOICE_FLAG_CODE);
        return $this;
    }

    /**
     * Aggregate Invoiced data by invoice created_at as period
     *
     * @param string|null $from
     * @param string|null $to
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    protected function _aggregateByInvoiceCreatedAt($from, $to)
    {
        $table = $this->getTable('sales_invoiced_aggregated');
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
            $columns = [
                'period' => $periodExpr,
                'store_id' => 'order_table.store_id',
                'order_status' => 'order_table.status',
                'orders_count' => new \Zend_Db_Expr('COUNT(order_table.entity_id)'),
                'orders_invoiced' => new \Zend_Db_Expr('COUNT(order_table.entity_id)'),
                'invoiced' => new \Zend_Db_Expr(
                    'SUM(order_table.base_total_invoiced * order_table.base_to_global_rate)'
                ),
                'invoiced_captured' => new \Zend_Db_Expr(
                    'SUM(order_table.base_total_paid * order_table.base_to_global_rate)'
                ),
                'invoiced_not_captured' => new \Zend_Db_Expr(
                    'SUM((order_table.base_total_invoiced - order_table.base_total_paid)' .
                    ' * order_table.base_to_global_rate)'
                ),
            ];

            $select = $connection->select();
            $select->from(
                ['source_table' => $sourceTable],
                $columns
            )->joinInner(
                ['order_table' => $orderTable],
                $connection->quoteInto(
                    'source_table.order_id = order_table.entity_id AND order_table.state <> ?',
                    \Magento\Sales\Model\Order::STATE_CANCELED
                ),
                []
            );

            $filterSubSelect = $connection->select();
            $filterSubSelect->from(
                ['filter_source_table' => $sourceTable],
                'MAX(filter_source_table.entity_id)'
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
                'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                'order_status' => 'order_status',
                'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
                'orders_invoiced' => new \Zend_Db_Expr('SUM(orders_invoiced)'),
                'invoiced' => new \Zend_Db_Expr('SUM(invoiced)'),
                'invoiced_captured' => new \Zend_Db_Expr('SUM(invoiced_captured)'),
                'invoiced_not_captured' => new \Zend_Db_Expr('SUM(invoiced_not_captured)'),
            ];

            $select->from($table, $columns)->where('store_id <> ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

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
     * Aggregate Invoiced data by order created_at as period
     *
     * @param string|null $from
     * @param string|null $to
     * @return $this
     * @since 2.0.0
     */
    protected function _aggregateByOrderCreatedAt($from, $to)
    {
        $table = $this->getTable('sales_invoiced_aggregated_order');
        $sourceTable = $this->getTable('sales_order');
        $connection = $this->getConnection();

        if ($from !== null || $to !== null) {
            $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $from, $to);
        } else {
            $subSelect = null;
        }

        $this->_clearTableByDateRange($table, $from, $to, $subSelect);
        // convert dates to current admin timezone
        $periodExpr = $connection->getDatePartSql($this->getStoreTZOffsetQuery($sourceTable, 'created_at', $from, $to));

        $columns = [
            'period' => $periodExpr,
            'store_id' => 'store_id',
            'order_status' => 'status',
            'orders_count' => new \Zend_Db_Expr('COUNT(base_total_invoiced)'),
            'orders_invoiced' => new \Zend_Db_Expr(
                sprintf('SUM(%s)', $connection->getCheckSql('base_total_invoiced > 0', 1, 0))
            ),
            'invoiced' => new \Zend_Db_Expr(
                sprintf(
                    'SUM(%s * %s)',
                    $connection->getIfNullSql('base_total_invoiced', 0),
                    $connection->getIfNullSql('base_to_global_rate', 0)
                )
            ),
            'invoiced_captured' => new \Zend_Db_Expr(
                sprintf(
                    'SUM(%s * %s)',
                    $connection->getIfNullSql('base_total_paid', 0),
                    $connection->getIfNullSql('base_to_global_rate', 0)
                )
            ),
            'invoiced_not_captured' => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s) * %s)',
                    $connection->getIfNullSql('base_total_invoiced', 0),
                    $connection->getIfNullSql('base_total_paid', 0),
                    $connection->getIfNullSql('base_to_global_rate', 0)
                )
            ),
        ];

        $select = $connection->select();
        $select->from($sourceTable, $columns)->where('state <> ?', \Magento\Sales\Model\Order::STATE_CANCELED);

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
            'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
            'order_status' => 'order_status',
            'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
            'orders_invoiced' => new \Zend_Db_Expr('SUM(orders_invoiced)'),
            'invoiced' => new \Zend_Db_Expr('SUM(invoiced)'),
            'invoiced_captured' => new \Zend_Db_Expr('SUM(invoiced_captured)'),
            'invoiced_not_captured' => new \Zend_Db_Expr('SUM(invoiced_not_captured)'),
        ];

        $select->from($table, $columns)->where('store_id <> ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

        if ($subSelect !== null) {
            $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
        }

        $select->group(['period', 'order_status']);
        $insertQuery = $select->insertFromSelect($table, array_keys($columns));
        $connection->query($insertQuery);
        return $this;
    }
}
