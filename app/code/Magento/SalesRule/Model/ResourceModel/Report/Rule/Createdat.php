<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\SalesRule\Model\ResourceModel\Report\Rule;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Tax\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Rule report resource model with aggregation by created at
 */
class Createdat extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{
    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param Config $taxConfig
     * @param string|null $connectionName
     */
    public function __construct(
        private readonly Context $context,
        private readonly LoggerInterface $logger,
        private readonly TimezoneInterface $localeDate,
        private readonly FlagFactory $reportsFlagFactory,
        private readonly Validator $timezoneValidator,
        DateTime $dateTime,
        private Config $taxConfig,
        ?string $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
    }

    /**
     * Resource Report Rule constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('salesrule_coupon_aggregated', 'id');
    }

    /**
     * Aggregate Coupons data by order created at
     *
     * @param mixed|null $from
     * @param mixed|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByOrder('created_at', $from, $to);
    }

    /**
     * Aggregate coupons reports by orders
     *
     * @throws \Exception
     * @param string $aggregationField
     * @param mixed $from
     * @param mixed $to
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _aggregateByOrder($aggregationField, $from, $to)
    {
        $table = $this->getMainTable();
        $sourceTable = $this->getTable('sales_order');
        $connection = $this->getConnection();
        $salesAdapter = $this->_resources->getConnection('sales');
        $connection->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $from, $to);
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect, false, $salesAdapter);

            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery($sourceTable, $aggregationField, $from, $to, null, $salesAdapter)
            );

            $subtotalAmountFiled = 'base_subtotal';
            $subtotalAmountActualFiled = 'base_subtotal_invoiced';
            if ($this->taxConfig->displaySalesSubtotalInclTax()) {
                $subtotalAmountFiled = 'base_subtotal_incl_tax';
                $subtotalAmountActualFiled = 'base_subtotal_incl_tax';
            }

            $columns = [
                'period' => $periodExpr,
                'store_id' => 'store_id',
                'order_status' => 'status',
                'coupon_code' => 'coupon_code',
                'rule_name' => 'coupon_rule_name',
                'coupon_uses' => 'COUNT(entity_id)',
                'subtotal_amount' => $connection->getIfNullSql(
                    'SUM((' . $subtotalAmountFiled . ' - ' . $connection->getIfNullSql(
                        'base_subtotal_canceled',
                        0
                    ) . ') * base_to_global_rate)',
                    0
                ),
                'discount_amount' => $connection->getIfNullSql(
                    'SUM((ABS(base_discount_amount) - ' . $connection->getIfNullSql(
                        'base_discount_canceled',
                        0
                    ) . ') * base_to_global_rate)',
                    0
                ),
                'total_amount' => $connection->getIfNullSql(
                    'SUM(((base_subtotal - ' . $connection->getIfNullSql(
                        'base_subtotal_canceled',
                        0
                    ) . ' + ' . $connection->getIfNullSql(
                        'base_shipping_amount - ' . $connection->getIfNullSql('base_shipping_canceled', 0),
                        0
                    ) . ') - ' . $connection->getIfNullSql(
                        'ABS(base_discount_amount) - ABS('
                        . $connection->getIfNullSql('base_discount_canceled', 0) . ')',
                        0
                    ) . ' + ' . $connection->getIfNullSql(
                        'base_tax_amount - ' . $connection->getIfNullSql('base_tax_canceled', 0),
                        0
                    ) . ' + ' . $connection->getIfNullSql(
                        'base_discount_tax_compensation_amount - '
                        . $connection->getIfNullSql('base_discount_tax_compensation_refunded', 0),
                        0
                    ) . ' - ' . $connection->getIfNullSql('ABS(base_shipping_discount_tax_compensation_amnt)', 0)
                    . ')
                        * base_to_global_rate)',
                    0
                ),
                'subtotal_amount_actual' => $connection->getIfNullSql(
                    'SUM((' . $subtotalAmountActualFiled . ' - ' . $connection->getIfNullSql(
                        'base_subtotal_refunded',
                        0
                    ) . ') * base_to_global_rate)',
                    0
                ),
                'discount_amount_actual' => $connection->getIfNullSql(
                    'SUM((base_discount_invoiced - ' . $connection->getIfNullSql(
                        'base_discount_refunded',
                        0
                    ) . ')
                        * base_to_global_rate)',
                    0
                ),
                'total_amount_actual' => $connection->getIfNullSql(
                    'SUM(((base_subtotal_invoiced - ' . $connection->getIfNullSql(
                        'base_subtotal_refunded',
                        0
                    ) . ' + ' . $connection->getIfNullSql(
                        'base_shipping_invoiced - ' . $connection->getIfNullSql('base_shipping_refunded', 0),
                        0
                    ) . ') - ' . $connection->getIfNullSql(
                        'ABS(base_discount_invoiced) - ABS('
                        . $connection->getIfNullSql('base_discount_refunded', 0) . ')',
                        0
                    ) . ' + ' . $connection->getIfNullSql(
                        'base_tax_invoiced - ' . $connection->getIfNullSql('base_tax_refunded', 0),
                        0
                    ) . ' + ' . $connection->getIfNullSql(
                        'base_discount_tax_compensation_invoiced - '
                        . $connection->getIfNullSql('base_discount_tax_compensation_refunded', 0),
                        0
                    ) . ' - ' . $connection->getIfNullSql('ABS(base_shipping_discount_tax_compensation_amnt)', 0)
                    . ')
                    * base_to_global_rate)',
                    0
                ),
            ];

            $select = $connection->select();
            $select->from(['source_table' => $sourceTable], $columns)->where('coupon_code IS NOT NULL');

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period', $salesAdapter));
            }

            $select->group([$periodExpr, 'store_id', 'status', 'coupon_code']);

            $select->having('COUNT(entity_id) > 0');

            $aggregatedData = $salesAdapter->fetchAll($select);

            if ($aggregatedData) {
                $connection->insertOnDuplicate($table, $aggregatedData, array_keys($columns));
            }

            $select->reset();

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr('0'),
                'order_status' => 'order_status',
                'coupon_code' => 'coupon_code',
                'rule_name' => 'rule_name',
                'coupon_uses' => 'SUM(coupon_uses)',
                'subtotal_amount' => 'SUM(subtotal_amount)',
                'discount_amount' => 'SUM(discount_amount)',
                'total_amount' => 'SUM(total_amount)',
                'subtotal_amount_actual' => 'SUM(subtotal_amount_actual)',
                'discount_amount_actual' => 'SUM(discount_amount_actual)',
                'total_amount_actual' => 'SUM(total_amount_actual)',
            ];

            $select->from($table, $columns)->where('store_id <> 0');

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period', $salesAdapter));
            }

            $select->group(['period', 'order_status', 'coupon_code']);

            $connection->query($select->insertFromSelect($table, array_keys($columns)));
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $this;
    }
}
