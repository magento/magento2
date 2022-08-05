<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Verify chart data by different period.
 *
 * @magentoAppArea adminhtml
 */
class ChartTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Chart
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Chart::class);
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
        $timezoneLocal = $this->objectManager->get(TimezoneInterface::class)->getConfigTimezone();
        $order = $this->objectManager->get(Order::class);
        $order->loadByIncrementId('100000002');
        $payment = $this->objectManager->get(Payment::class);
        $payment->setMethod('checkmo');
        $payment->setAdditionalInformation('last_trans_id', '11122');
        $payment->setAdditionalInformation('metadata', [
            'type' => 'free',
            'fraudulent' => false
        ]);
        $dateTime = new \DateTime('now', new \DateTimeZone($timezoneLocal));
        $order->setCreatedAt($dateTime->modify('-1 hour')->format(DateTime::DATETIME_PHP_FORMAT));
        $order->setPayment($payment);
        $order->save();
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
                1,
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
