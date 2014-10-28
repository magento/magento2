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
 * Reports Mysql resource helper model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource;

class Helper extends \Magento\Framework\DB\Helper implements \Magento\Reports\Model\Resource\HelperInterface
{
    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\Resource $resource, $modulePrefix = 'reports')
    {
        parent::__construct($resource, $modulePrefix);
    }

    /**
     * Merge Index data
     *
     * @param string $mainTable
     * @param array $data
     * @param mixed $matchFields
     * @return string
     */
    public function mergeVisitorProductIndex($mainTable, $data, $matchFields)
    {
        $result = $this->_getWriteAdapter()->insertOnDuplicate($mainTable, $data, array_keys($data));
        return $result;
    }

    /**
     * Update rating position
     *
     * @param string $type day|month|year
     * @param string $column
     * @param string $mainTable
     * @param string $aggregationTable
     * @return $this
     */
    public function updateReportRatingPos($type, $column, $mainTable, $aggregationTable)
    {
        $adapter = $this->_getWriteAdapter();
        $periodSubSelect = $adapter->select();
        $ratingSubSelect = $adapter->select();
        $ratingSelect = $adapter->select();

        switch ($type) {
            case 'year':
                $periodCol = $adapter->getDateFormatSql('t.period', '%Y-01-01');
                break;
            case 'month':
                $periodCol = $adapter->getDateFormatSql('t.period', '%Y-%m-01');
                break;
            default:
                $periodCol = 't.period';
                break;
        }

        $columns = array(
            'period' => 't.period',
            'store_id' => 't.store_id',
            'product_id' => 't.product_id',
            'product_name' => 't.product_name',
            'product_price' => 't.product_price'
        );

        if ($type == 'day') {
            $columns['id'] = 't.id';  // to speed-up insert on duplicate key update
        }

        $cols = array_keys($columns);
        $cols['total_qty'] = new \Zend_Db_Expr('SUM(t.' . $column . ')');
        $periodSubSelect->from(
            array('t' => $mainTable),
            $cols
        )->group(
            array('t.store_id', $periodCol, 't.product_id')
        )->order(
            array('t.store_id', $periodCol, 'total_qty DESC')
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
        $adapter->query("SET @pos = 0, @prevStoreId = -1, @prevPeriod = '0000-00-00'");
        $adapter->query($sql);
        return $this;
    }
}
