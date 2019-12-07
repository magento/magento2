<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Shipping\Collection;

/**
 * Integration tests for shipments reports collection which is used to obtain shipment reports by order date.
 */
class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Shipping\Collection\Order
     */
    private $collection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->collection = $this->objectManager->create(
            \Magento\Sales\Model\ResourceModel\Report\Shipping\Collection\Order::class
        );
        $this->collection->setPeriod('day')
            ->setDateRange(null, null)
            ->addStoreFilter([1]);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_shipping.php
     * @magentoDataFixture Magento/Sales/_files/order_from_past.php
     * @magentoDataFixture Magento/Sales/_files/report_shipping.php
     * @return void
     */
    public function testGetItems()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTimeFactory::class)
            ->create();
        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone */
        $timezone = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $orderCreatedAt = $timezone->formatDateTime(
            $order->getCreatedAt(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null,
            'yyyy-MM-dd'
        );
        $shipmentCreatedAtDate = $dateTime->date('Y-m-d', $orderCreatedAt);

        $expectedResult = [
            [
                'orders_count' => 1,
                'total_shipping' => 36,
                'total_shipping_actual' => 34,
                'period' => $shipmentCreatedAtDate
            ],
        ];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
