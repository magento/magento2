<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Report\Product\Viewed;

/**
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection
     */
    private $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection'
        );
        $this->_collection->setPeriod('day')
            ->setDateRange(null, null)
            ->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Reports/_files/viewed_products.php
     */
    public function testGetItems()
    {
        $expectedResult = [1 => 3, 2 => 1, 21 => 2];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[$reportItem->getData('product_id')] = $reportItem->getData('views_num');
        }
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @dataProvider tableForPeriodDataProvider
     *
     * @param $period
     * @param $expectedTable
     * @param $dateFrom
     * @param $dateTo
     * @param $isTotal
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testTableSelection($period, $expectedTable, $dateFrom, $dateTo, $isTotal = false)
    {
        $dbTableName = $this->_collection->getTable($expectedTable);
        $this->_collection->setPeriod($period);
        if ($isTotal != false) {
            $this->_collection->setAggregatedColumns(['id']);
            $this->_collection->isTotals(true);
        }
        $this->_collection->setDateRange($dateFrom, $dateTo);
        $this->_collection->load();
        $from = $this->_collection->getSelect()->getPart('from');

        if ($isTotal != false) {
            $this->assertArrayHasKey('t', $from);
            $this->assertArrayHasKey('tableName', $from['t']);
        } elseif (!empty($from) && is_array($from)) {
            $this->assertArrayHasKey($dbTableName, $from);
            $actualTable = $from[$dbTableName]['tableName'];
            $this->assertEquals($dbTableName, $actualTable);
            $this->assertArrayHasKey('tableName', $from[$dbTableName]);
        } else {
            $union = $this->_collection->getSelect()->getPart('union');
            if ($period !== null && $dateFrom !== null && $dateTo !== null && $period != 'month') {
                $count = count($union);
                if ($period == 'year') {
                    if ($dbTableName == "report_viewed_product_aggregated_daily") {
                        $this->assertEquals($count, 2);
                    }
                    if ($dbTableName == "report_viewed_product_aggregated_yearly") {
                        $this->assertEquals($count, 3);
                    }
                } else {
                    $this->assertEquals($count, 3);
                }
            } else {
                $this->assertEquals(count($union), 2);
            }
        }
    }

    /**
     * Data provider for testTableSelection
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function tableForPeriodDataProvider()
    {
        $dateNow = date('Y-m-d', time());
        $dateYearAgo = date('Y-m-d', strtotime($dateNow . ' -1 year'));
        return [
            [
                'period'    => 'year',
                'table'     => 'report_viewed_product_aggregated_yearly',
                'date_from' => null,
                'date_to'   => null,
                'is_total'  => true,
            ],
            [
                'period'    => 'year',
                'table'     => 'report_viewed_product_aggregated_yearly',
                'date_from' => $dateYearAgo,
                'date_to'   => $dateNow,
            ],
            [
                'period'    => 'year',
                'table'     => 'report_viewed_product_aggregated_yearly',
                'date_from' => $dateYearAgo,
                'date_to'   => null,
            ],
            [
                'period'    => 'month',
                'table'     => 'report_viewed_product_aggregated_monthly',
                'date_from' => null,
                'date_to'   => $dateNow,
            ],
            [
                'period'    => 'year',
                'table'     => 'report_viewed_product_aggregated_yearly',
                'date_from' => $dateYearAgo,
                'date_to'   => null,
            ],
            [
                'period'    => 'year',
                'table'     => 'report_viewed_product_aggregated_yearly',
                'date_from' => null,
                'date_to'   => $dateNow,
            ],
            [
                'period'    => 'month',
                'table'     => 'report_viewed_product_aggregated_monthly',
                'date_from' => null,
                'date_to'   => null,
            ],
            [
                'period'    => 'month',
                'table'     => 'report_viewed_product_aggregated_monthly',
                'date_from' => $dateYearAgo,
                'date_to'   => $dateYearAgo,
            ],
            [
                'period'    => 'month',
                'table'     => 'report_viewed_product_aggregated_monthly',
                'date_from' => null,
                'date_to'   => $dateYearAgo,
            ],
            [
                'period'    => 'month',
                'table'     => 'report_viewed_product_aggregated_monthly',
                'date_from' => $dateYearAgo,
                'date_to'   => null,
            ],
            [
                'period'    => 'day',
                'table'     => 'report_viewed_product_aggregated_daily',
                'date_from' => null,
                'date_to'   => null,
            ],
            [
                'period'    => 'undefinedPeriod',
                'table'     => 'report_viewed_product_aggregated_daily',
                'date_from' => null,
                'date_to'   => null,
            ],
            [
                'period'    => null,
                'table'     => 'report_viewed_product_aggregated_daily',
                'date_from' => $dateYearAgo,
                'date_to'   => $dateNow,
            ],
            [
                'period'    => null,
                'table'     => 'report_viewed_product_aggregated_daily',
                'date_from' => $dateNow,
                'date_to'   => $dateNow,
            ],
            [
                'period'    => 'day',
                'table'     => 'report_viewed_product_aggregated_daily',
                'date_from' => $dateYearAgo,
                'date_to'   => $dateYearAgo,
            ],
            [
                'period'    => 'year',
                'table'     => 'report_viewed_product_aggregated_daily',
                'date_from' => $dateYearAgo,
                'date_to'   => $dateYearAgo,
            ],
            [
                'period'    => 'year',
                'table'     => 'report_viewed_product_aggregated_daily',
                'date_from' => null,
                'date_to'   => $dateYearAgo,
            ],
            [
                'period'    => null,
                'table'     => 'report_viewed_product_aggregated_yearly',
                'date_from' => null,
                'date_to'   => null,
            ]
        ];
    }
}
