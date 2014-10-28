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
namespace Magento\Sales\Model\Resource\Report;

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
     * @param string|int|\Zend_Date|array|null $from
     * @param string|int|\Zend_Date|array|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
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
        $sourceTable = $this->getTable('sales_flat_order');
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $from, $to);
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $adapter->getDatePartSql(
                $this->getStoreTZOffsetQuery($sourceTable, 'created_at', $from, $to)
            );
            $columns = array(
                'period' => $periodExpr,
                'store_id' => 'store_id',
                'order_status' => 'status',
                'orders_count' => new \Zend_Db_Expr('COUNT(total_refunded)'),
                'refunded' => new \Zend_Db_Expr('SUM(base_total_refunded * base_to_global_rate)'),
                'online_refunded' => new \Zend_Db_Expr('SUM(base_total_online_refunded * base_to_global_rate)'),
                'offline_refunded' => new \Zend_Db_Expr('SUM(base_total_offline_refunded * base_to_global_rate)')
            );

            $select = $adapter->select();
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

            $select->group(array($periodExpr, 'store_id', 'status'));
            $select->having('orders_count > 0');
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
            $select->reset();

            $columns = array(
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr('0'),
                'order_status' => 'order_status',
                'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
                'refunded' => new \Zend_Db_Expr('SUM(refunded)'),
                'online_refunded' => new \Zend_Db_Expr('SUM(online_refunded)'),
                'offline_refunded' => new \Zend_Db_Expr('SUM(offline_refunded)')
            );

            $select->from($table, $columns)->where('store_id != ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array('period', 'order_status'));
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
            $adapter->commit();
        } catch (\Exception $e) {
            $adapter->rollBack();
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
     */
    protected function _aggregateByRefundCreatedAt($from, $to)
    {
        $table = $this->getTable('sales_refunded_aggregated');
        $sourceTable = $this->getTable('sales_flat_creditmemo');
        $orderTable = $this->getTable('sales_flat_order');
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeRelatedSelect(
                    $sourceTable,
                    $orderTable,
                    array('order_id' => 'entity_id'),
                    'created_at',
                    'updated_at',
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $adapter->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    array('source_table' => $sourceTable),
                    'source_table.created_at',
                    $from,
                    $to
                )
            );

            $columns = array(
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
                )
            );

            $select = $adapter->select();
            $select->from(
                array('source_table' => $sourceTable),
                $columns
            )->joinInner(
                array('order_table' => $orderTable),
                'source_table.order_id = order_table.entity_id AND ' . $adapter->quoteInto(
                    'order_table.state != ?',
                    \Magento\Sales\Model\Order::STATE_CANCELED
                ) . ' AND order_table.base_total_refunded > 0',
                array()
            );

            $filterSubSelect = $adapter->select();
            $filterSubSelect->from(
                array('filter_source_table' => $sourceTable),
                new \Zend_Db_Expr('MAX(filter_source_table.entity_id)')
            )->where(
                'filter_source_table.order_id = source_table.order_id'
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->where('source_table.entity_id = (?)', new \Zend_Db_Expr($filterSubSelect));
            unset($filterSubSelect);

            $select->group(array($periodExpr, 'order_table.store_id', 'order_table.status'));
            $select->having('orders_count > 0');

            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
            $select->reset();

            $columns = array(
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr('0'),
                'order_status' => 'order_status',
                'orders_count' => new \Zend_Db_Expr('SUM(orders_count)'),
                'refunded' => new \Zend_Db_Expr('SUM(refunded)'),
                'online_refunded' => new \Zend_Db_Expr('SUM(online_refunded)'),
                'offline_refunded' => new \Zend_Db_Expr('SUM(offline_refunded)')
            );

            $select->from($table, $columns)->where('store_id != ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array('period', 'order_status'));
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
        } catch (\Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
        $adapter->commit();
        return $this;
    }
}
