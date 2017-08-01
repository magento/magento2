<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports Mysql resource helper model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel;

/**
 * Class \Magento\Reports\Model\ResourceModel\Helper
 *
 * @since 2.0.0
 */
class Helper extends \Magento\Framework\DB\Helper implements \Magento\Reports\Model\ResourceModel\HelperInterface
{
    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource, $modulePrefix = 'reports')
    {
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
     * @since 2.0.0
     */
    public function mergeVisitorProductIndex($mainTable, $data, $matchFields)
    {
        $result = $this->getConnection()->insertOnDuplicate($mainTable, $data, array_keys($data));
        return $result;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function updateReportRatingPos($connection, $type, $column, $mainTable, $aggregationTable)
    {
        $periodSubSelect = $connection->select();
        $ratingSubSelect = $connection->select();
        $ratingSelect = $connection->select();

        switch ($type) {
            case 'year':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-01-01');
                break;
            case 'month':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-%m-01');
                break;
            default:
                $periodCol = 't.period';
                break;
        }

        $columns = [
            'period' => 't.period',
            'store_id' => 't.store_id',
            'product_id' => 't.product_id',
            'product_name' => 't.product_name',
            'product_price' => 't.product_price',
        ];

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
        $cols[$column] = 't.' . $column;
        $cols['rating_pos'] = 't.rating_pos';
        $ratingSelect->from($ratingSubSelect, $cols);

        $sql = $ratingSelect->insertFromSelect($aggregationTable, array_keys($cols));
        $connection->query("SET @pos = 0, @prevStoreId = -1, @prevPeriod = '0000-00-00'");
        $connection->query($sql);
        return $this;
    }
}
