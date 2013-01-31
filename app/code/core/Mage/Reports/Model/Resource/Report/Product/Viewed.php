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
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Most viewed product report aggregate resource model
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Report_Product_Viewed extends Mage_Sales_Model_Resource_Report_Abstract
{
    const AGGREGATION_DAILY   = 'report_viewed_product_aggregated_daily';
    const AGGREGATION_MONTHLY = 'report_viewed_product_aggregated_monthly';
    const AGGREGATION_YEARLY  = 'report_viewed_product_aggregated_yearly';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init(self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate products view data
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Resource_Report_Bestsellers
     */
    public function aggregate($from = null, $to = null)
    {
        $mainTable   = $this->getMainTable();
        $adapter = $this->_getWriteAdapter();

        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);

        if ($from !== null || $to !== null) {
            $subSelect = $this->_getTableDateRangeSelect(
                $this->getTable('report_event'),
                'logged_at', 'logged_at', $from, $to
            );
        } else {
            $subSelect = null;
        }
        $this->_clearTableByDateRange($mainTable, $from, $to, $subSelect);
        // convert dates from UTC to current admin timezone
        $periodExpr = $adapter->getDatePartSql(
            $this->getStoreTZOffsetQuery(
                array('source_table' => $this->getTable('report_event')),
                'source_table.logged_at', $from, $to
            )
        );

        $helper = Mage::getResourceHelper('Mage_Core');
        $select = $adapter->select();

        $select->group(array(
            $periodExpr,
            'source_table.store_id',
            'source_table.object_id'
        ));

        $viewsNumExpr = new Zend_Db_Expr('COUNT(source_table.event_id)');

        $columns = array(
            'period'                 => $periodExpr,
            'store_id'               => 'source_table.store_id',
            'product_id'             => 'source_table.object_id',
            'product_name'           => new Zend_Db_Expr(
                sprintf('MIN(%s)',
                    $adapter->getIfNullSql('product_name.value','product_default_name.value')
                )
            ),
            'product_price'          => new Zend_Db_Expr(
                sprintf('%s',
                    $helper->prepareColumn(
                        sprintf('MIN(%s)',
                            $adapter->getIfNullSql(
                                $adapter->getIfNullSql('product_price.value','product_default_price.value'), 0)
                        ),
                        $select->getPart(Zend_Db_Select::GROUP)
                    )
                )
            ),
            'views_num'            => $viewsNumExpr
        );

        $select
            ->from(
                array(
                    'source_table' => $this->getTable('report_event')),
                $columns)
            ->where('source_table.event_type_id = ?', Mage_Reports_Model_Event::EVENT_PRODUCT_VIEW);

        /** @var Mage_Catalog_Model_Resource_Product $product */
        $product  = Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product');

        $select->joinInner(
            array(
                'product' => $this->getTable('catalog_product_entity')),
            'product.entity_id = source_table.object_id',
            array()
        );

        // join product attributes Name & Price
        $nameAttribute = $product->getAttribute('name');
        $joinExprProductName       = array(
            'product_name.entity_id = product.entity_id',
            'product_name.store_id = source_table.store_id',
            $adapter->quoteInto('product_name.attribute_id = ?', $nameAttribute->getAttributeId())
        );
        $joinExprProductName        = implode(' AND ', $joinExprProductName);
        $joinExprProductDefaultName = array(
            'product_default_name.entity_id = product.entity_id',
            'product_default_name.store_id = 0',
            $adapter->quoteInto('product_default_name.attribute_id = ?', $nameAttribute->getAttributeId())
        );
        $joinExprProductDefaultName = implode(' AND ', $joinExprProductDefaultName);
        $select->joinLeft(
            array(
                'product_name' => $nameAttribute->getBackend()->getTable()),
            $joinExprProductName,
            array()
        )
        ->joinLeft(
            array(
                'product_default_name' => $nameAttribute->getBackend()->getTable()),
            $joinExprProductDefaultName,
            array()
        );
        $priceAttribute                    = $product->getAttribute('price');
        $joinExprProductPrice    = array(
            'product_price.entity_id = product.entity_id',
            'product_price.store_id = source_table.store_id',
            $adapter->quoteInto('product_price.attribute_id = ?', $priceAttribute->getAttributeId())
        );
        $joinExprProductPrice    = implode(' AND ', $joinExprProductPrice);

        $joinExprProductDefPrice = array(
            'product_default_price.entity_id = product.entity_id',
            'product_default_price.store_id = 0',
            $adapter->quoteInto('product_default_price.attribute_id = ?', $priceAttribute->getAttributeId())
        );
        $joinExprProductDefPrice = implode(' AND ', $joinExprProductDefPrice);
        $select->joinLeft(
            array('product_price' => $priceAttribute->getBackend()->getTable()),
            $joinExprProductPrice,
            array()
        )
        ->joinLeft(
            array('product_default_price' => $priceAttribute->getBackend()->getTable()),
            $joinExprProductDefPrice,
            array()
        );

        $havingPart = array($adapter->prepareSqlCondition($viewsNumExpr, array('gt' => 0)));
        if ($subSelect !== null) {
            $subSelectHavingPart = $this->_makeConditionFromDateRangeSelect($subSelect, 'period');
            if ($subSelectHavingPart) {
                $havingPart[] = '(' . $subSelectHavingPart . ')';
            }
        }
        $select->having(implode(' AND ', $havingPart));

        $select->useStraightJoin();
        $insertQuery = $helper->getInsertFromSelectUsingAnalytic($select, $this->getMainTable(),
            array_keys($columns));
        $adapter->query($insertQuery);

        Mage::getResourceHelper('Mage_Reports')
            ->updateReportRatingPos('day', 'views_num', $mainTable, $this->getTable(self::AGGREGATION_DAILY));
        Mage::getResourceHelper('Mage_Reports')
            ->updateReportRatingPos('month', 'views_num', $mainTable, $this->getTable(self::AGGREGATION_MONTHLY));
        Mage::getResourceHelper('Mage_Reports')
            ->updateReportRatingPos('year', 'views_num', $mainTable, $this->getTable(self::AGGREGATION_YEARLY));

        $this->_setFlagData(Mage_Reports_Model_Flag::REPORT_PRODUCT_VIEWED_FLAG_CODE);

        return $this;
    }
}
