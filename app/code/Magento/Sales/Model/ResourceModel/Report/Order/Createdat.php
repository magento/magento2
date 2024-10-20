<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Order;

/**
 * Order entity resource model with aggregation by created at
 */
class Createdat extends \Magento\Sales\Model\ResourceModel\Report\AbstractReport
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_aggregated_created', 'id');
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByField('created_at', $from, $to);
    }

    /**
     * Aggregate Orders data by custom field
     *
     * @param string $aggregationField
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _aggregateByField($aggregationField, $from, $to)
    {
        $connection = $this->getConnection();

        $connection->beginTransaction();
        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales_order'),
                    $aggregationField,
                    $aggregationField,
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }
            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);

            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    ['o' => $this->getTable('sales_order')],
                    'o.' . $aggregationField,
                    $from,
                    $to
                )
            );
            // Columns list
            $columns = [
                'period' => $periodExpr,
                'store_id' => 'o.store_id',
                'order_status' => 'o.status',
                'orders_count' => new \Zend_Db_Expr('COUNT(o.entity_id)'),
                'total_qty_ordered' => new \Zend_Db_Expr('SUM(oi.total_qty_ordered)'),
                'total_qty_invoiced' => new \Zend_Db_Expr('SUM(oi.total_qty_invoiced)'),
                'total_income_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s - %s) * %s)',
                        $connection->getIfNullSql('o.base_grand_total', 0),
                        $connection->getIfNullSql('o.base_total_canceled', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_revenue_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s - %s - %s - (%s - %s - %s)) * %s)',
                        $connection->getIfNullSql('o.base_total_invoiced', 0),
                        $connection->getIfNullSql('o.base_tax_invoiced', 0),
                        $connection->getIfNullSql('o.base_shipping_invoiced', 0),
                        $connection->getIfNullSql('o.base_total_refunded', 0),
                        $connection->getIfNullSql('o.base_tax_refunded', 0),
                        $connection->getIfNullSql('o.base_shipping_refunded', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_profit_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s - %s - %s - %s - %s) * %s)',
                        $connection->getIfNullSql('o.base_total_paid', 0),
                        $connection->getIfNullSql('o.base_total_refunded', 0),
                        $connection->getIfNullSql('o.base_tax_invoiced', 0),
                        $connection->getIfNullSql('o.base_shipping_invoiced', 0),
                        $connection->getIfNullSql('o.base_total_invoiced_cost', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_invoiced_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM(%s * %s)',
                        $connection->getIfNullSql('o.base_total_invoiced', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_canceled_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM(%s * %s)',
                        $connection->getIfNullSql('o.base_total_canceled', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_paid_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM(%s * %s)',
                        $connection->getIfNullSql('o.base_total_paid', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_refunded_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM(%s * %s)',
                        $connection->getIfNullSql('o.base_total_refunded', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s - %s) * %s)',
                        $connection->getIfNullSql('o.base_tax_amount', 0),
                        $connection->getIfNullSql('o.base_tax_canceled', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount_actual' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s -%s) * %s)',
                        $connection->getIfNullSql('o.base_tax_invoiced', 0),
                        $connection->getIfNullSql('o.base_tax_refunded', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s - %s) * %s)',
                        $connection->getIfNullSql('o.base_shipping_amount', 0),
                        $connection->getIfNullSql('o.base_shipping_canceled', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount_actual' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s - %s) * %s)',
                        $connection->getIfNullSql('o.base_shipping_invoiced', 0),
                        $connection->getIfNullSql('o.base_shipping_refunded', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((ABS(%s) - %s) * %s)',
                        $connection->getIfNullSql('o.base_discount_amount', 0),
                        $connection->getIfNullSql('o.base_discount_canceled', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount_actual' => new \Zend_Db_Expr(
                    sprintf(
                        'SUM((%s - %s) * %s)',
                        $connection->getIfNullSql('o.base_discount_invoiced', 0),
                        $connection->getIfNullSql('o.base_discount_refunded', 0),
                        $connection->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
            ];

            $select = $connection->select();
            $selectOrderItem = $connection->select();

            $qtyCanceledExpr = $connection->getIfNullSql('qty_canceled', 0);
            $cols = [
                'order_id' => 'order_id',
                'total_qty_ordered' => new \Zend_Db_Expr("SUM(qty_ordered - {$qtyCanceledExpr})"),
                'total_qty_invoiced' => new \Zend_Db_Expr('SUM(qty_invoiced)'),
            ];
            $selectOrderItem->from(
                $this->getTable('sales_order_item'),
                $cols
            )->where(
                'parent_item_id IS NULL'
            )->group(
                'order_id'
            );

            $select->from(
                ['o' => $this->getTable('sales_order')],
                $columns
            )->join(
                ['oi' => $selectOrderItem],
                'oi.order_id = o.entity_id',
                []
            )->where(
                'o.state NOT IN (?)',
                [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group([$periodExpr, 'o.store_id', 'o.status']);

            $connection->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

            // setup all columns to select SUM() except period, store_id and order_status
            foreach ($columns as $k => $v) {
                $columns[$k] = new \Zend_Db_Expr('SUM(' . $k . ')');
            }
            $columns['period'] = 'period';
            $columns['store_id'] = new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $columns['order_status'] = 'order_status';

            $select->reset();
            $select->from($this->getMainTable(), $columns)->where('store_id <> 0');

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'order_status']);
            $connection->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $this;
    }
}
