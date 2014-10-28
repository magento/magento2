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


/**
 * Tax report resource model with aggregation by created at
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\Resource\Report\Tax;

class Createdat extends \Magento\Reports\Model\Resource\Report\AbstractReport
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
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales_flat_order'),
                    'created_at',
                    'updated_at',
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $writeAdapter->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    array('e' => $this->getTable('sales_flat_order')),
                    'e.' . $aggregationField,
                    $from,
                    $to
                )
            );

            $columns = array(
                'period' => $periodExpr,
                'store_id' => 'e.store_id',
                'code' => 'tax.code',
                'order_status' => 'e.status',
                'percent' => 'MAX(tax.' . $writeAdapter->quoteIdentifier('percent') . ')',
                'orders_count' => 'COUNT(DISTINCT e.entity_id)',
                'tax_base_amount_sum' => 'SUM(tax.base_amount * e.base_to_global_rate)'
            );

            $select = $writeAdapter->select();
            $select->from(
                array('tax' => $this->getTable('sales_order_tax')),
                $columns
            )->joinInner(
                array('e' => $this->getTable('sales_flat_order')),
                'e.entity_id = tax.order_id',
                array()
            )->useStraightJoin();

            $select->where(
                'e.state NOT IN (?)',
                array(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW)
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array($periodExpr, 'e.store_id', 'code', 'tax.percent', 'e.status'));

            $insertQuery = $writeAdapter->insertFromSelect($select, $this->getMainTable(), array_keys($columns));
            $writeAdapter->query($insertQuery);

            $select->reset();

            $columns = array(
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                'code' => 'code',
                'order_status' => 'order_status',
                'percent' => 'MAX(' . $writeAdapter->quoteIdentifier('percent') . ')',
                'orders_count' => 'SUM(orders_count)',
                'tax_base_amount_sum' => 'SUM(tax_base_amount_sum)'
            );

            $select->from($this->getMainTable(), $columns)->where('store_id <> ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array('period', 'code', 'percent', 'order_status'));
            $insertQuery = $writeAdapter->insertFromSelect($select, $this->getMainTable(), array_keys($columns));
            $writeAdapter->query($insertQuery);
            $writeAdapter->commit();
        } catch (\Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        return $this;
    }
}
