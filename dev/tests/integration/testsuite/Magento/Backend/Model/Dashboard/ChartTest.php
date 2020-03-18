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
    protected function setUp()
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
        $this->assertCount($expectedDataQty, $this->model->getByPeriod($period, $chartParam));
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
                24,
                '24h',
                'quantity'
            ],
            [
                8,
                '7d',
                'quantity'
            ],
            [
                19,
                '1m',
                'quantity'
            ],
            [
                16,
                '1y',
                'quantity'
            ],
            [
                28,
                '2y',
                'quantity'
            ]
        ];
    }
}
