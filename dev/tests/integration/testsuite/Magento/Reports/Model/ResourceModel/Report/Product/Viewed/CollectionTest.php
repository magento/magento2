<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Report\Product\Viewed;

/**
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection
     */
    private $_collection;

    protected function setUp(): void
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection::class
        );
        $this->_collection->setPeriod('day')
            ->setDateRange(null, null)
            ->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Reports/_files/viewed_products.php
     * @magentoConfigFixture default/reports/options/enabled 1
     */
    public function testGetItems()
    {
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[$reportItem->getData('product_id')] = $reportItem->getData('views_num');
        }
        $this->assertNotEmpty($actualResult);
        $this->assertCount(3, $actualResult);
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
            $count = count($union);
            if ($period !== null && $dateFrom !== null && $dateTo !== null && $period != 'month') {
                if ($period == 'year') {
                    if ($dbTableName == "report_viewed_product_aggregated_daily") {
                        $this->assertEquals(2, $count);
                    }
                    if ($dbTableName == "report_viewed_product_aggregated_yearly") {
                        $this->assertEquals(3, $count);
                    }
                } else {
                    $this->assertEquals(3, $count);
                }
            } else {
                $this->assertEquals(2, $count);
            }
        }
    }

    /**
     * Data provider for testTableSelection
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function tableForPeriodDataProvider()
    {
        $dateFrom = '2019-10-15';
        $dateYearBefore = date('Y-m-d', strtotime($dateFrom . ' -1 year'));
        return [
            [
                'period'    => 'year',
                'expectedTable'     => 'report_viewed_product_aggregated_yearly',
                'dateFrom' => null,
                'dateTo'   => null,
                'isTotal'  => true,
            ],
            [
                'period'    => 'year',
                'expectedTable'     => 'report_viewed_product_aggregated_yearly',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => $dateFrom,
            ],
            [
                'period'    => 'year',
                'expectedTable'     => 'report_viewed_product_aggregated_yearly',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => null,
            ],
            [
                'period'    => 'month',
                'expectedTable'     => 'report_viewed_product_aggregated_monthly',
                'dateFrom' => null,
                'dateTo'   => $dateFrom,
            ],
            [
                'period'    => 'year',
                'expectedTable'     => 'report_viewed_product_aggregated_yearly',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => null,
            ],
            [
                'period'    => 'year',
                'expectedTable'     => 'report_viewed_product_aggregated_yearly',
                'dateFrom' => null,
                'dateTo'   => $dateFrom,
            ],
            [
                'period'    => 'month',
                'expectedTable'     => 'report_viewed_product_aggregated_monthly',
                'dateFrom' => null,
                'dateTo'   => null,
            ],
            [
                'period'    => 'month',
                'expectedTable'     => 'report_viewed_product_aggregated_monthly',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => $dateYearBefore,
            ],
            [
                'period'    => 'month',
                'expectedTable'     => 'report_viewed_product_aggregated_monthly',
                'dateFrom' => null,
                'dateTo'   => $dateYearBefore,
            ],
            [
                'period'    => 'month',
                'expectedTable'     => 'report_viewed_product_aggregated_monthly',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => null,
            ],
            [
                'period'    => 'day',
                'expectedTable'     => 'report_viewed_product_aggregated_daily',
                'dateFrom' => null,
                'dateTo'   => null,
            ],
            [
                'period'    => 'undefinedPeriod',
                'expectedTable'     => 'report_viewed_product_aggregated_daily',
                'dateFrom' => null,
                'dateTo'   => null,
            ],
            [
                'period'    => null,
                'expectedTable'     => 'report_viewed_product_aggregated_daily',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => $dateFrom,
            ],
            [
                'period'    => null,
                'expectedTable'     => 'report_viewed_product_aggregated_daily',
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateFrom,
            ],
            [
                'period'    => 'day',
                'expectedTable'     => 'report_viewed_product_aggregated_daily',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => $dateYearBefore,
            ],
            [
                'period'    => 'year',
                'expectedTable'     => 'report_viewed_product_aggregated_daily',
                'dateFrom' => $dateYearBefore,
                'dateTo'   => $dateYearBefore,
            ],
            [
                'period'    => 'year',
                'expectedTable'     => 'report_viewed_product_aggregated_daily',
                'dateFrom' => null,
                'dateTo'   => $dateYearBefore,
            ],
            [
                'period'    => null,
                'expectedTable'     => 'report_viewed_product_aggregated_yearly',
                'dateFrom' => null,
                'dateTo'   => null,
            ]
        ];
    }
}
