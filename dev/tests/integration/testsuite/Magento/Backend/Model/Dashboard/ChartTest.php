<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify chart data by different period.
 *
 * @magentoAppArea adminhtml
 */
class ChartTest extends TestCase
{
    /**
     * @var Chart
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(Chart::class);
    }

    /**
     * Verify getByPeriod with all types of period
     *
     * @magentoDataFixture Magento/Sales/_files/order_list_with_invoice.php
     * @dataProvider getChartDataProvider
     * @return void
     */
    public function testGetByPeriodWithParam(int $expectedDataQty, string $period, string $chartParam): void
    {
        $ordersData = $this->model->getByPeriod($period, $chartParam);
        $ordersCount = array_sum(array_map(function ($item) {
            return $item['y'];
        }, $ordersData));
        $this->assertGreaterThanOrEqual($expectedDataQty, $ordersCount);
    }

    /**
     * Expected chart data
     *
     * @return array
     */
    public function getChartDataProvider(): array
    {
        return [
            [
                2,
                '24h',
                'quantity'
            ],
            [
                3,
                '7d',
                'quantity'
            ],
            [
                4,
                '1m',
                'quantity'
            ],
            [
                5,
                '1y',
                'quantity'
            ],
            [
                6,
                '2y',
                'quantity'
            ]
        ];
    }
}
