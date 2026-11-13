<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Reports Mysql resource helper model
 */
namespace Magento\Reports\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class Helper extends \Magento\Framework\DB\Helper implements \Magento\Reports\Model\ResourceModel\HelperInterface
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param string $modulePrefix
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        string $modulePrefix = 'reports'
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($resource, $modulePrefix);
    }

    /**
     * Merge Index data
     *
     * @param string $mainTable
     * @param array $data
     * @param mixed $matchFields
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function mergeVisitorProductIndex($mainTable, $data, $matchFields)
    {
        $result = $this->getConnection()->insertOnDuplicate($mainTable, $data, array_keys($data));
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function updateReportRatingPos($connection, $type, $column, $mainTable, $aggregationTable)
    {
        return $this->updateRatingPos($connection, $type, $column, $mainTable, $aggregationTable, null);
    }

    /**
     * Update rating position only for the periods that contain the given dates
     *
     * The periods are days, months or years depending on $type. When any value is not a valid Y-m-d
     * date, every period is recalculated.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string|null $type
     * @param string $column
     * @param string $mainTable
     * @param string $aggregationTable
     * @param array $periods Dates in Y-m-d format
     * @return $this
     */
    public function updateReportRatingPosForPeriods(
        $connection,
        $type,
        $column,
        $mainTable,
        $aggregationTable,
        array $periods
    ) {
        if ($periods === []) {
            return $this;
        }

        return $this->updateRatingPos(
            $connection,
            $type,
            $column,
            $mainTable,
            $aggregationTable,
            $this->getPeriodsCondition($connection, $type, $periods)
        );
    }

    /**
     * Update rating position of the main table rows matching the condition, or of all rows when null
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string|null $type
     * @param string $column
     * @param string $mainTable
     * @param string $aggregationTable
     * @param string|null $periodsCondition
     * @return $this
     */
    private function updateRatingPos(
        $connection,
        $type,
        $column,
        $mainTable,
        $aggregationTable,
        ?string $periodsCondition
    ) {
        foreach ($this->storeManager->getStores(true) as $store) {
            $periodSubSelect = $connection->select();
            $ratingSubSelect = $connection->select();
            $ratingSelect = $connection->select();

            $columnExists = $connection->tableColumnExists($mainTable, 'period_month');
            $columns = [
                'period' => 't.period',
                'store_id' => 't.store_id',
                'product_id' => 't.product_id',
                'product_name' => 't.product_name',
                'product_price' => 't.product_price'
            ];

            switch ($type) {
                case 'year':
                    $periodCol = $connection->getDateFormatSql('t.period', '%Y-01-01');
                    break;
                case 'month':
                    if ($columnExists) {
                        $periodCol = 't.period_month';
                        $columns['period_month'] = $periodCol;
                        unset($columns['period']);
                    } else {
                        $periodCol = $connection->getDateFormatSql('t.period', '%Y-%m-01');
                    }
                    break;
                default:
                    $periodCol = 't.period';
                    break;
            }

            if ($type == 'day') {
                $columns['id'] = 't.id';  // to speed-up insert on duplicate key update
            }

            $cols = array_keys($columns);
            $cols['total_qty'] = new \Zend_Db_Expr('SUM(t.' . $column . ')');
            $periodSubSelect->from(
                ['t' => $mainTable],
                $cols
            )->group(
                ['t.store_id', $periodCol, 't.product_id']
            )->order(
                ['t.store_id', $periodCol, 'total_qty DESC']
            );
            if ($periodsCondition !== null) {
                $periodSubSelect->where($periodsCondition);
            }

            $cols = $columns;
            $cols[$column] = 't.total_qty';
            $cols['rating_pos'] = new \Zend_Db_Expr(
                "(@pos := IF(t.`store_id` <> @prevStoreId OR {$periodCol} <> @prevPeriod, 1, @pos+1))"
            );
            $cols['prevStoreId'] = new \Zend_Db_Expr('(@prevStoreId := t.`store_id`)');
            $cols['prevPeriod'] = new \Zend_Db_Expr("(@prevPeriod := {$periodCol})");
            $ratingSubSelect->from($periodSubSelect, $cols);

            $cols = $columns;
            $cols['period'] = $periodCol;
            if (isset($cols['period_month'])) {
                unset($cols['period_month']);
            }
            $cols[$column] = 't.' . $column;
            $cols['rating_pos'] = 't.rating_pos';

            $ratingSubSelect->where('t.store_id = ' . $store->getId());
            $ratingSelect->from($ratingSubSelect, $cols);

            $sql = $ratingSelect->insertFromSelect($aggregationTable, array_keys($cols));
            $connection->query("SET @pos = 0, @prevStoreId = -1, @prevPeriod = '0000-00-00'");
            $connection->query($sql);
        }

        return $this;
    }

    /**
     * Build a condition on the main table rows that belong to the periods containing the given dates
     *
     * Returns null when any value is not a valid Y-m-d date.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string|null $type
     * @param array $dates
     * @return string|null
     */
    private function getPeriodsCondition($connection, ?string $type, array $dates): ?string
    {
        $ranges = [];
        foreach ($dates as $value) {
            $date = $this->parseDate($value);
            if ($date === null) {
                return null;
            }
            switch ($type) {
                case 'year':
                    $ranges[$date->format('Y-01-01')] = $date->format('Y-12-31');
                    break;
                case 'month':
                    $ranges[$date->format('Y-m-01')] = $date->format('Y-m-t');
                    break;
                default:
                    $ranges[$date->format('Y-m-d')] = $date->format('Y-m-d');
                    break;
            }
        }
        ksort($ranges);

        if (!in_array($type, ['year', 'month'], true)) {
            return $connection->prepareSqlCondition('t.period', ['in' => array_keys($ranges)]);
        }

        $conditions = [];
        foreach ($ranges as $start => $end) {
            $conditions[] = '(' . $connection->prepareSqlCondition('t.period', ['from' => $start, 'to' => $end]) . ')';
        }

        return implode(' OR ', $conditions);
    }

    /**
     * Parse a Y-m-d date, rejecting any other value and dates that do not exist
     *
     * @param mixed $value
     * @return \DateTimeImmutable|null
     */
    private function parseDate($value): ?\DateTimeImmutable
    {
        if (!is_string($value)) {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $date : null;
    }
}
