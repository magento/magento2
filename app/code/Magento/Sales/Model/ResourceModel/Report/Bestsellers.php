<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Helper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Bestsellers report resource model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bestsellers extends AbstractReport
{
    public const AGGREGATION_DAILY = 'daily';

    public const AGGREGATION_MONTHLY = 'monthly';

    public const AGGREGATION_YEARLY = 'yearly';

    /**
     * @var Product
     */
    protected $_productResource;

    /**
     * @var Helper
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param Product $productResource
     * @param Helper $salesResourceHelper
     * @param string|null $connectionName
     * @param array $ignoredProductTypes
     * @param StoreManagerInterface|null $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        DateTime $dateTime,
        Product $productResource,
        Helper $salesResourceHelper,
        ?string $connectionName = null,
        array $ignoredProductTypes = [],
        ?StoreManagerInterface $storeManager = null
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
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
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
     */
    public function aggregate($from = null, $to = null)
    {
        $connection = $this->getConnection();
        $this->clearByDateRange($from, $to);
        foreach ($this->storeManager->getStores(true) as $store) {
            $this->processStoreAggregate($store->getId(), $from, $to);
        }

        $columns = [
            'period' => 'period',
            'store_id' => new \Zend_Db_Expr(Store::DEFAULT_STORE_ID),
            'product_id' => 'product_id',
            'product_name' => new \Zend_Db_Expr('MIN(product_name)'),
            'product_price' => new \Zend_Db_Expr('MIN(product_price)'),
            'qty_ordered' => new \Zend_Db_Expr('SUM(qty_ordered)'),
        ];

        $select = $connection->select();
        $select->reset();
        $select->from(
            $this->getMainTable(),
            $columns
        )->where(
            'store_id <> ?',
            Store::DEFAULT_STORE_ID
        );
        $subSelect = $this->getRangeSubSelect($from, $to);
        if ($subSelect !== null) {
            $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
        }

        $select->group(['period', 'product_id']);
        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);

        $this->_updateRatingPos(self::AGGREGATION_DAILY);
        $this->_updateRatingPos(self::AGGREGATION_MONTHLY);
        $this->_updateRatingPos(self::AGGREGATION_YEARLY);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_BESTSELLERS_FLAG_CODE);

        return $this;
    }

    /**
     * Clear aggregate existing data by range
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return void
     * @throws LocalizedException
     */
    private function clearByDateRange($from = null, $to = null): void
    {
        $subSelect = $this->getRangeSubSelect($from, $to);
        $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);
    }

    /**
     * Get report range sub-select
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return Select|null
     */
    private function getRangeSubSelect($from = null, $to = null): ?Select
    {
        $subSelect = null;
        if ($from !== null || $to !== null) {
            $subSelect = $this->_getTableDateRangeSelect(
                $this->getTable('sales_order'),
                'created_at',
                'updated_at',
                $from,
                $to
            );
        }

        return $subSelect;
    }

    /**
     * Calculate report aggregate per store
     *
     * @param int|null $storeId
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return void
     * @throws LocalizedException
     */
    private function processStoreAggregate(?int $storeId, $from = null, $to = null): void
    {
        $connection = $this->getConnection();

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
        $subSelect = $this->getRangeSubSelect($from, $to);

        $select->group([$periodExpr, 'source_table.store_id', 'order_item.product_id']);

        $columns = [
            'period' => $periodExpr,
            'store_id' => 'source_table.store_id',
            'product_id' => 'order_item.product_id',
            'product_name' => new \Zend_Db_Expr('MIN(order_item.name)'),
            'product_price' => new \Zend_Db_Expr(
                'MIN(IF(order_item_parent.base_price, order_item_parent.base_price, order_item.base_price))' .
                '* MIN(source_table.base_to_global_rate)'
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
        )->joinLeft(
            ['order_item_parent' => $this->getTable('sales_order_item')],
            'order_item.parent_item_id = order_item_parent.item_id',
            []
        )->where(
            "source_table.entity_id IN (SELECT entity_id FROM " . $this->getTable('sales_order') .
            " WHERE store_id = " . $storeId .
            " AND state != '" . \Magento\Sales\Model\Order::STATE_CANCELED . "'" .
            ($subSelect !== null ?
                " AND " . $this->_makeConditionFromDateRangeSelect($subSelect, $periodExpr) :
                '') . ")"
        )->where(
            'order_item.product_type NOT IN(?)',
            $this->ignoredProductTypes
        );

        $select->useStraightJoin();
        // important!
        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);
    }

    /**
     * Update rating position
     *
     * @param string $aggregation
     * @return $this
     * @throws LocalizedException
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
