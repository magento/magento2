<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report;

/**
 * Bestsellers report resource model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bestsellers extends AbstractReport
{
    const AGGREGATION_DAILY = 'daily';

    const AGGREGATION_MONTHLY = 'monthly';

    const AGGREGATION_YEARLY = 'yearly';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResource;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Helper
     */
    protected $_salesResourceHelper;

    /**
     * Ignored product types list
     *
     * @var array
     */
    protected $ignoredProductTypes = [
        \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
    ];

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Sales\Model\ResourceModel\Helper $salesResourceHelper
     * @param array $ignoredProductTypes
     * @param string $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Sales\Model\ResourceModel\Helper $salesResourceHelper,
        $connectionName = null,
        array $ignoredProductTypes = []
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
        $this->_productResource = $productResource;
        $this->_salesResourceHelper = $salesResourceHelper;
        $this->ignoredProductTypes = array_merge($this->ignoredProductTypes, $ignoredProductTypes);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_bestsellers_aggregated_' . self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aggregate($from = null, $to = null)
    {
        $connection = $this->getConnection();
        //$this->getConnection()->beginTransaction();

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

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);
            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    ['source_table' => $this->getTable('sales_order')],
                    'source_table.created_at',
                    $from,
                    $to
                )
            );
            $select = $connection->select();

            $select->group([$periodExpr, 'source_table.store_id', 'order_item.product_id']);

            $columns = [
                'period' => $periodExpr,
                'store_id' => 'source_table.store_id',
                'product_id' => 'order_item.product_id',
                'product_name' => new \Zend_Db_Expr('MIN(order_item.name)'),
                'product_price' => new \Zend_Db_Expr(
                    'MIN(order_item.base_price) * MIN(source_table.base_to_global_rate)'
                ),
                'qty_ordered' => new \Zend_Db_Expr('SUM(order_item.qty_ordered)'),
            ];

            $select->from(
                ['source_table' => $this->getTable('sales_order')],
                $columns
            )->joinInner(
                ['order_item' => $this->getTable('sales_order_item')],
                'order_item.order_id = source_table.entity_id',
                []
            )->where(
                'source_table.state != ?',
                \Magento\Sales\Model\Order::STATE_CANCELED
            )->where(
                'order_item.product_type NOT IN(?)',
                $this->ignoredProductTypes
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->useStraightJoin();
            // important!
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                'product_id' => 'product_id',
                'product_name' => new \Zend_Db_Expr('MIN(product_name)'),
                'product_price' => new \Zend_Db_Expr('MIN(product_price)'),
                'qty_ordered' => new \Zend_Db_Expr('SUM(qty_ordered)'),
            ];

            $select->reset();
            $select->from(
                $this->getMainTable(),
                $columns
            )->where(
                'store_id <> ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'product_id']);
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $connection->query($insertQuery);

            // update rating
            $this->_updateRatingPos(self::AGGREGATION_DAILY);
            $this->_updateRatingPos(self::AGGREGATION_MONTHLY);
            $this->_updateRatingPos(self::AGGREGATION_YEARLY);
            $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_BESTSELLERS_FLAG_CODE);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Update rating position
     *
     * @param string $aggregation
     * @return $this
     */
    protected function _updateRatingPos($aggregation)
    {
        $aggregationTable = $this->getTable('sales_bestsellers_aggregated_' . $aggregation);

        $aggregationAliases = [
            'daily' => self::AGGREGATION_DAILY,
            'monthly' => self::AGGREGATION_MONTHLY,
            'yearly' => self::AGGREGATION_YEARLY,
        ];
        $this->_salesResourceHelper->getBestsellersReportUpdateRatingPos(
            $aggregation,
            $aggregationAliases,
            $this->getMainTable(),
            $aggregationTable
        );
        return $this;
    }
}
