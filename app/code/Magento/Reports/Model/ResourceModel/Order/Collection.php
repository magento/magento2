<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Model\ResourceModel\Order;

use Magento\Framework\DB\Select;
use DateTimeZone;

/**
 * Reports orders collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    /**
     * @var bool
     */
    protected $_isLive = false;

    /**
     * The sales amount expression
     *
     * @var string
     */
    protected $_salesAmountExpression;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager instance
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Locale date instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Order config instance
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * Reports order factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Report\OrderFactory
     */
    protected $_reportOrderFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Helper $coreResourceHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Sales\Model\ResourceModel\Report\OrderFactory $reportOrderFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Report\OrderFactory $reportOrderFactory,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $connection,
            $resource
        );
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_orderConfig = $orderConfig;
        $this->_reportOrderFactory = $reportOrderFactory;
    }

    /**
     * Check range for live mode
     *
     * @param string $range
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkIsLive($range)
    {
        $this->_isLive = (bool)(!$this->_scopeConfig->getValue(
            'sales/dashboard/use_aggregated_data',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        return $this;
    }

    /**
     * Retrieve is live flag for rep
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isLive()
    {
        return $this->_isLive;
    }

    /**
     * Prepare report summary
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return $this
     */
    public function prepareSummary($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->checkIsLive($range);
        if ($this->_isLive) {
            $this->_prepareSummaryLive($range, $customStart, $customEnd, $isFilter);
        } else {
            $this->_prepareSummaryAggregated($range, $customStart, $customEnd);
        }

        return $this;
    }

    /**
     * Get sales amount expression
     *
     * @return string
     */
    protected function _getSalesAmountExpression()
    {
        if (null === $this->_salesAmountExpression) {
            $connection = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '%s - %s - %s - (%s - %s - %s)',
                    'arguments' => [
                        $connection->getIfNullSql('main_table.base_total_invoiced', 0),
                        $connection->getIfNullSql('main_table.base_tax_invoiced', 0),
                        $connection->getIfNullSql('main_table.base_shipping_invoiced', 0),
                        $connection->getIfNullSql('main_table.base_total_refunded', 0),
                        $connection->getIfNullSql('main_table.base_tax_refunded', 0),
                        $connection->getIfNullSql('main_table.base_shipping_refunded', 0),
                    ],
                ]
            );

            $this->_eventManager->dispatch(
                'sales_prepare_amount_expression',
                ['collection' => $this, 'expression_object' => $expressionTransferObject]
            );
            $this->_salesAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_salesAmountExpression;
    }

    /**
     * Prepare report summary from live data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return $this
     */
    protected function _prepareSummaryLive($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->setMainTable('sales_order');
        $connection = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $expression = $this->_getSalesAmountExpression();
        if ($isFilter == 0) {
            $this->getSelect()->columns(
                [
                    'revenue' => new \Zend_Db_Expr(
                        sprintf(
                            'SUM((%s) * %s)',
                            $expression,
                            $connection->getIfNullSql('main_table.base_to_global_rate', 0)
                        )
                    ),
                ]
            );
        } else {
            $this->getSelect()->columns(['revenue' => new \Zend_Db_Expr(sprintf('SUM(%s)', $expression))]);
        }

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
            $range,
            'created_at',
            $dateRange['from'],
            $dateRange['to']
        );

        $this->getSelect()->columns(
            ['quantity' => 'COUNT(main_table.entity_id)', 'range' => $tzRangeOffsetExpression]
        )->where(
            'main_table.state NOT IN (?)',
            [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
        )->order(
            'range',
            \Magento\Framework\DB\Select::SQL_ASC
        )->group(
            $tzRangeOffsetExpression
        );

        $this->addFieldToFilter('created_at', $dateRange);

        return $this;
    }

    /**
     * Prepare report summary from aggregated data
     *
     * @param string $range
     * @param string|null $customStart
     * @param string|null $customEnd
     * @return $this
     */
    protected function _prepareSummaryAggregated($range, $customStart, $customEnd)
    {
        $this->setMainTable('sales_order_aggregated_created');
        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $rangePeriod = $this->_getRangeExpressionForAttribute($range, 'main_table.period');

        $tableName = $this->getConnection()->quoteIdentifier('main_table.period');
        $rangePeriodAggregateStmt = str_replace($tableName, "MIN({$tableName})", $rangePeriod);

        $this->getSelect()->columns(
            [
                'revenue' => 'SUM(main_table.total_revenue_amount)',
                'quantity' => 'SUM(main_table.orders_count)',
                'range' => $rangePeriodAggregateStmt,
            ]
        )->order(
            'range'
        )->group(
            $rangePeriod
        );

        $this->getSelect()->where(
            $this->_getConditionSql('main_table.period', $this->getDateRange($range, $customStart, $customEnd))
        );

        $statuses = $this->_orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = [0];
        }
        $this->addFieldToFilter('main_table.order_status', ['nin' => $statuses]);

        return $this;
    }

    /**
     * Get range expression
     *
     * @param string $range
     * @return \Zend_Db_Expr
     */
    protected function _getRangeExpression($range)
    {
        switch ($range) {
            case 'today':
            case '24h':
                $expression = $this->getConnection()->getConcatSql(
                    [
                        $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                        $this->getConnection()->quote('00'),
                    ]
                );
                break;
            case '7d':
            case '1m':
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m');
                break;
        }

        return $expression;
    }

    /**
     * Retrieve range expression adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @return string
     */
    protected function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), (string) $expression);
    }

    /**
     * Retrieve query for attribute with timezone conversion
     *
     * @param string $range
     * @param string $attribute
     * @param string|null $from
     * @param string|null $to
     * @return string
     */
    protected function _getTZRangeOffsetExpression($range, $attribute, $from = null, $to = null)
    {
        return str_replace(
            '{{attribute}}',
            $this->_reportOrderFactory->create()->getStoreTZOffsetQuery($this->getMainTable(), $attribute, $from, $to),
            (string) $this->_getRangeExpression($range)
        );
    }

    /**
     * Retrieve range expression with timezone conversion adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @param string $tzFrom
     * @param string $tzTo
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getTZRangeExpressionForAttribute($range, $attribute, $tzFrom = '+00:00', $tzTo = null)
    {
        if (null == $tzTo) {
            $tzTo = $this->_localeDate->scopeDate()->format('P');
        }
        $connection = $this->getConnection();
        $expression = $this->_getRangeExpression($range);
        $attribute = $connection->quoteIdentifier($attribute);
        $periodExpr = $connection->getDateAddSql(
            $attribute,
            $tzTo,
            \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_HOUR
        );

        return str_replace('{{attribute}}', $periodExpr, $expression);
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param bool $returnObjects
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = new \DateTime();
        $timezoneLocal = $this->_localeDate->getConfigTimezone();

        $dateEnd->setTimezone(new DateTimeZone($timezoneLocal));

        // go to the end of a day
        $dateEnd->setTime(23, 59, 59);

        $dateStart = clone $dateEnd;
        $dateStart->setTime(0, 0, 0);

        switch ($range) {
            case 'today':
                $dateEnd = new \DateTime('now', new \DateTimeZone($timezoneLocal));
                break;
            case '24h':
                $dateEnd = new \DateTime('now', new \DateTimeZone($timezoneLocal));
                $dateEnd->modify('+1 hour');
                $dateStart = clone $dateEnd;
                $dateStart->modify('-1 day');
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->modify('-6 days');
                break;

            case '1m':
                $dateStart->setDate(
                    $dateStart->format('Y'),
                    $dateStart->format('m'),
                    $this->_scopeConfig->getValue(
                        'reports/dashboard/mtd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateStart;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(
                    ',',
                    $this->_scopeConfig->getValue(
                        'reports/dashboard/ytd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setDate($dateStart->format('Y'), $startMonth, $startDay);
                if ($range == '2y') {
                    $dateStart->modify('-1 year');
                }
                break;
        }
        $dateStart->setTimezone(new DateTimeZone('UTC'));
        $dateEnd->setTimezone(new DateTimeZone('UTC'));
        if ($returnObjects) {
            return [$dateStart, $dateEnd];
        } else {
            return ['from' => $dateStart, 'to' => $dateEnd, 'datetime' => true];
        }
    }

    /**
     * Add item count expression
     *
     * @return $this
     */
    public function addItemCountExpr()
    {
        $this->getSelect()->columns(['items_count' => 'total_item_count'], 'main_table');
        return $this;
    }

    /**
     * Calculate totals report
     *
     * @param int $isFilter
     * @return $this
     */
    public function calculateTotals($isFilter = 0)
    {
        if ($this->isLive()) {
            $this->_calculateTotalsLive($isFilter);
        } else {
            $this->_calculateTotalsAggregated($isFilter);
        }

        return $this;
    }

    /**
     * Calculate totals live report
     *
     * @param int $isFilter
     * @return $this
     */
    protected function _calculateTotalsLive($isFilter = 0)
    {
        $this->setMainTable('sales_order');
        $this->removeAllFieldsFromSelect();

        $connection = $this->getConnection();

        $baseTaxInvoiced = $connection->getIfNullSql('main_table.base_tax_invoiced', 0);
        $baseTaxRefunded = $connection->getIfNullSql('main_table.base_tax_refunded', 0);
        $baseShippingInvoiced = $connection->getIfNullSql('main_table.base_shipping_invoiced', 0);
        $baseShippingRefunded = $connection->getIfNullSql('main_table.base_shipping_refunded', 0);

        $revenueExp = $this->_getSalesAmountExpression();
        $taxExp = sprintf('%s - %s', $baseTaxInvoiced, $baseTaxRefunded);
        $shippingExp = sprintf('%s - %s', $baseShippingInvoiced, $baseShippingRefunded);

        if ($isFilter == 0) {
            $rateExp = $connection->getIfNullSql('main_table.base_to_global_rate', 0);
            $this->getSelect()->columns(
                [
                    'revenue' => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $revenueExp, $rateExp)),
                    'tax' => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $taxExp, $rateExp)),
                    'shipping' => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $shippingExp, $rateExp)),
                ]
            );
        } else {
            $this->getSelect()->columns(
                [
                    'revenue' => new \Zend_Db_Expr(sprintf('SUM(%s)', $revenueExp)),
                    'tax' => new \Zend_Db_Expr(sprintf('SUM(%s)', $taxExp)),
                    'shipping' => new \Zend_Db_Expr(sprintf('SUM(%s)', $shippingExp)),
                ]
            );
        }

        $this->getSelect()->columns(
            ['quantity' => 'COUNT(main_table.entity_id)']
        )->where(
            'main_table.state NOT IN (?)',
            [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
        );

        return $this;
    }

    /**
     * Calculate totals aggregated report
     *
     * @param int $isFilter
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _calculateTotalsAggregated($isFilter = 0)
    {
        $this->setMainTable('sales_order_aggregated_created');
        $this->removeAllFieldsFromSelect();

        $this->getSelect()->columns(
            [
                'revenue' => 'SUM(main_table.total_revenue_amount)',
                'tax' => 'SUM(main_table.total_tax_amount_actual)',
                'shipping' => 'SUM(main_table.total_shipping_amount_actual)',
                'quantity' => 'SUM(orders_count)',
            ]
        );

        $statuses = $this->_orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = [0];
        }

        $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);

        return $this;
    }

    /**
     * Calculate lifitime sales
     *
     * @param int $isFilter
     * @return $this
     */
    public function calculateSales($isFilter = 0)
    {
        $statuses = $this->_orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = [0];
        }
        $connection = $this->getConnection();

        if ($this->_scopeConfig->getValue(
            'sales/dashboard/use_aggregated_data',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            $this->setMainTable('sales_order_aggregated_created');
            $this->removeAllFieldsFromSelect();
            $averageExpr = $connection->getCheckSql(
                'SUM(main_table.orders_count) > 0',
                'SUM(main_table.total_revenue_amount)/SUM(main_table.orders_count)',
                0
            );
            $this->getSelect()->columns(
                ['lifetime' => 'SUM(main_table.total_revenue_amount)', 'average' => $averageExpr]
            );

            if (!$isFilter) {
                $this->addFieldToFilter(
                    'store_id',
                    ['eq' => $this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId()]
                );
            }
            $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);
        } else {
            $this->setMainTable('sales_order');
            $this->removeAllFieldsFromSelect();

            $expr = $this->_getSalesAmountExpression();

            if ($isFilter == 0) {
                $expr = '(' . $expr . ') * main_table.base_to_global_rate';
            }

            $this->getSelect()->columns(
                ['lifetime' => "SUM({$expr})", 'average' => "AVG({$expr})"]
            )->where(
                'main_table.status NOT IN(?)',
                $statuses
            )->where(
                'main_table.state NOT IN(?)',
                [\Magento\Sales\Model\Order::STATE_NEW, \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT]
            );
        }
        return $this;
    }

    /**
     * Set date range
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function setDateRange($fromDate, $toDate)
    {
        $this->_reset()->addFieldToFilter(
            'created_at',
            ['from' => $fromDate, 'to' => $toDate]
        )->addFieldToFilter(
            'state',
            ['neq' => \Magento\Sales\Model\Order::STATE_CANCELED]
        )->getSelect()->columns(
            ['orders' => 'COUNT(DISTINCT(main_table.entity_id))']
        )->group(
            'entity_id'
        );

        $this->getSelect()->columns(['items' => 'SUM(main_table.total_qty_ordered)']);

        return $this;
    }

    /**
     * Set store filter collection
     *
     * @param int[] $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $connection = $this->getConnection();
        $baseSubtotalInvoiced = $connection->getIfNullSql('main_table.base_subtotal_invoiced', 0);
        $baseDiscountRefunded = $connection->getIfNullSql('main_table.base_discount_refunded', 0);
        $baseSubtotalRefunded = $connection->getIfNullSql('main_table.base_subtotal_refunded', 0);
        $baseDiscountInvoiced = $connection->getIfNullSql('main_table.base_discount_invoiced', 0);
        $baseTotalInvocedCost = $connection->getIfNullSql('main_table.base_total_invoiced_cost', 0);
        if ($storeIds) {
            $this->getSelect()->columns(
                [
                    'subtotal' => 'SUM(main_table.base_subtotal)',
                    'tax' => 'SUM(main_table.base_tax_amount)',
                    'shipping' => 'SUM(main_table.base_shipping_amount)',
                    'discount' => 'SUM(main_table.base_discount_amount)',
                    'total' => 'SUM(main_table.base_grand_total)',
                    'invoiced' => 'SUM(main_table.base_total_paid)',
                    'refunded' => 'SUM(main_table.base_total_refunded)',
                    'profit' => "SUM({$baseSubtotalInvoiced}) " .
                    "+ SUM({$baseDiscountRefunded}) - SUM({$baseSubtotalRefunded}) " .
                    "- SUM({$baseDiscountInvoiced}) - SUM({$baseTotalInvocedCost})",
                ]
            );
        } else {
            $this->getSelect()->columns(
                [
                    'subtotal' => 'SUM(main_table.base_subtotal * main_table.base_to_global_rate)',
                    'tax' => 'SUM(main_table.base_tax_amount * main_table.base_to_global_rate)',
                    'shipping' => 'SUM(main_table.base_shipping_amount * main_table.base_to_global_rate)',
                    'discount' => 'SUM(main_table.base_discount_amount * main_table.base_to_global_rate)',
                    'total' => 'SUM(main_table.base_grand_total * main_table.base_to_global_rate)',
                    'invoiced' => 'SUM(main_table.base_total_paid * main_table.base_to_global_rate)',
                    'refunded' => 'SUM(main_table.base_total_refunded * main_table.base_to_global_rate)',
                    'profit' => "SUM({$baseSubtotalInvoiced} *  main_table.base_to_global_rate) " .
                    "+ SUM({$baseDiscountRefunded} * main_table.base_to_global_rate) " .
                    "- SUM({$baseSubtotalRefunded} * main_table.base_to_global_rate) " .
                    "- SUM({$baseDiscountInvoiced} * main_table.base_to_global_rate) " .
                    "- SUM({$baseTotalInvocedCost} * main_table.base_to_global_rate)",
                ]
            );
        }

        return $this;
    }

    /**
     * Add group By customer attribute
     *
     * @return $this
     */
    public function groupByCustomer()
    {
        $this->getSelect()->where('main_table.customer_id IS NOT NULL')->group('main_table.customer_id');
        return $this;
    }

    /**
     * Join Customer Name (concat)
     *
     * @param string $alias
     * @return $this
     */
    public function joinCustomerName($alias = 'name')
    {
        $fields = ['main_table.customer_firstname', 'main_table.customer_lastname'];
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns([$alias => $fieldConcat]);
        return $this;
    }

    /**
     * Add Order count field to select
     *
     * @return $this
     */
    public function addOrdersCount()
    {
        $this->addFieldToFilter('state', ['neq' => \Magento\Sales\Model\Order::STATE_CANCELED]);
        $this->getSelect()->columns(['orders_count' => 'COUNT(main_table.entity_id)']);

        return $this;
    }

    /**
     * Add revenue
     *
     * @param bool $convertCurrency
     * @return $this
     */
    public function addRevenueToSelect($convertCurrency = false)
    {
        $expr = $this->getTotalsExpressionWithDiscountRefunded(
            !$convertCurrency,
            $this->getConnection()->getIfNullSql('main_table.base_subtotal_refunded', 0),
            $this->getConnection()->getIfNullSql('main_table.base_subtotal_canceled', 0),
            $this->getConnection()->getIfNullSql('ABS(main_table.base_discount_refunded)', 0),
            $this->getConnection()->getIfNullSql('ABS(main_table.base_discount_canceled)', 0)
        );
        $this->getSelect()->columns(['revenue' => $expr]);

        return $this;
    }

    /**
     * Add summary average totals
     *
     * @param int $storeId
     * @return $this
     */
    public function addSumAvgTotals($storeId = 0)
    {
        /**
         * calculate average and total amount
         */
        $expr = $this->getTotalsExpressionWithDiscountRefunded(
            $storeId,
            $this->getConnection()->getIfNullSql('main_table.base_subtotal_refunded', 0),
            $this->getConnection()->getIfNullSql('main_table.base_subtotal_canceled', 0),
            $this->getConnection()->getIfNullSql('ABS(main_table.base_discount_refunded)', 0),
            $this->getConnection()->getIfNullSql('ABS(main_table.base_discount_canceled)', 0)
        );

        $this->getSelect()->columns(
            ['orders_avg_amount' => "AVG({$expr})"]
        )->columns(
            ['orders_sum_amount' => "SUM({$expr})"]
        );

        return $this;
    }

    /**
     * Get SQL expression for totals.
     *
     * @param int $storeId
     * @param string $baseSubtotalRefunded
     * @param string $baseSubtotalCanceled
     * @param string $baseDiscountCanceled
     * @return string
     * @deprecated 100.3.2
     * @see getTotalsExpressionWithDiscountRefunded
     */
    protected function getTotalsExpression(
        $storeId,
        $baseSubtotalRefunded,
        $baseSubtotalCanceled,
        $baseDiscountCanceled
    ) {
        $template = ($storeId != 0)
            ? '(main_table.base_subtotal - %2$s - %1$s - ABS(main_table.base_discount_amount) - %3$s)'
            : '((main_table.base_subtotal - %1$s - %2$s - ABS(main_table.base_discount_amount) + %3$s) '
            . ' * main_table.base_to_global_rate)';
        return sprintf($template, $baseSubtotalRefunded, $baseSubtotalCanceled, $baseDiscountCanceled);
    }

    /**
     * Get SQL expression for totals with discount refunded.
     *
     * @param int $storeId
     * @param string $baseSubtotalRefunded
     * @param string $baseSubtotalCanceled
     * @param string $baseDiscountRefunded
     * @param string $baseDiscountCanceled
     * @return string
     */
    private function getTotalsExpressionWithDiscountRefunded(
        $storeId,
        $baseSubtotalRefunded,
        $baseSubtotalCanceled,
        $baseDiscountRefunded,
        $baseDiscountCanceled
    ) {
        $template = ($storeId != 0)
            ? '(main_table.base_subtotal - %2$s - %1$s - (ABS(main_table.base_discount_amount) - %3$s - %4$s))'
            : '((main_table.base_subtotal - %1$s - %2$s - (ABS(main_table.base_discount_amount) - %3$s - %4$s)) '
                . ' * main_table.base_to_global_rate)';
        return sprintf(
            $template,
            $baseSubtotalRefunded,
            $baseSubtotalCanceled,
            $baseDiscountRefunded,
            $baseDiscountCanceled
        );
    }

    /**
     * Sort order by total amount
     *
     * @param string $dir
     * @return $this
     */
    public function orderByTotalAmount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order('orders_sum_amount ' . $dir);
        return $this;
    }

    /**
     * Order by orders count
     *
     * @param string $dir
     * @return $this
     */
    public function orderByOrdersCount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order('orders_count ' . $dir);
        return $this;
    }

    /**
     * Order by customer registration
     *
     * @param string $dir
     * @return $this
     */
    public function orderByCustomerRegistration($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('customer_id', $dir);
        return $this;
    }

    /**
     * Sort order by order created_at date
     *
     * @param string $dir
     * @return $this
     */
    public function orderByCreatedAt($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('created_at', $dir);
        return $this;
    }

    /**
     * Get select count sql
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->columns("COUNT(DISTINCT main_table.entity_id)");

        return $countSelect;
    }

    /**
     * Initialize initial fields to select
     *
     * @return $this
     * @codeCoverageIgnore
     */
    protected function _initInitialFieldsToSelect()
    {
        // No fields should be initialized
        return $this;
    }

    /**
     * Add period filter by created_at attribute
     *
     * @param string $period
     * @return $this
     */
    public function addCreateAtPeriodFilter($period)
    {
        list($from, $to) = $this->getDateRange($period, 0, 0, true);

        $this->checkIsLive($period);

        if ($this->isLive()) {
            $fieldToFilter = 'created_at';
        } else {
            $fieldToFilter = 'period';
        }

        $this->addFieldToFilter(
            $fieldToFilter,
            [
                'from' => $from->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                'to' => $to->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ]
        );

        return $this;
    }
}
