<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Bestsellers;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
     */
    private $_collection;

    protected function setUp(): void
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection::class
        );
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Sales/_files/report_bestsellers.php
     */
    public function testGetItems()
    {
        $expectedResult = [1 => 2];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[$reportItem->getData('product_id')] = $reportItem->getData('qty_ordered');
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
     */
    public function testTableSelection($period, $expectedTable, $dateFrom, $dateTo)
    {
        $dbTableName = $this->_collection->getTable($expectedTable);
        $this->_collection->setPeriod($period);
        $this->_collection->setDateRange($dateFrom, $dateTo);
        $this->_collection->load();
        $from = $this->_collection->getSelect()->getPart('from');

        $this->assertArrayHasKey($dbTableName, $from);

        $this->assertArrayHasKey('tableName', $from[$dbTableName]);
        $actualTable = $from[$dbTableName]['tableName'];

        $this->assertEquals($dbTableName, $actualTable);
    }

    /**
     * Data provider for testTableSelection
     *
     * @return array
     */
    public static function tableForPeriodDataProvider()
    {
        $dateNow = date('Y-m-d', time());
        $dateYearAgo = date('Y-m-d', strtotime($dateNow . ' -1 year'));
        return [
            [
                'period'    => 'year',
                'expectedTable'     => 'sales_bestsellers_aggregated_yearly',
                'dateFrom' => null,
                'dateTo'   => null,
            ],
            [
                'period'    => 'month',
                'expectedTable'     => 'sales_bestsellers_aggregated_monthly',
                'dateFrom' => null,
                'dateTo'   => null
            ],
            [
                'period'    => 'day',
                'expectedTable'     => 'sales_bestsellers_aggregated_daily',
                'dateFrom' => null,
                'dateTo'   => null
            ],
            [
                'period'    => 'undefinedPeriod',
                'expectedTable'     => 'sales_bestsellers_aggregated_daily',
                'dateFrom' => null,
                'dateTo'   => null
            ],
            [
                'period'    => null,
                'expectedTable'     => 'sales_bestsellers_aggregated_daily',
                'dateFrom' => $dateYearAgo,
                'dateTo'   => $dateNow
            ],
            [
                'period'    => null,
                'expectedTable'     => 'sales_bestsellers_aggregated_daily',
                'dateFrom' => $dateNow,
                'dateTo'   => $dateNow
            ],
            [
                'period'    => null,
                'expectedTable'     => 'sales_bestsellers_aggregated_daily',
                'dateFrom' => $dateYearAgo,
                'dateTo'   => $dateYearAgo
            ],
            [
                'period'    => null,
                'expectedTable'     => 'sales_bestsellers_aggregated_yearly',
                'dateFrom' => null,
                'dateTo'   => null
            ],
        ];
    }
}
