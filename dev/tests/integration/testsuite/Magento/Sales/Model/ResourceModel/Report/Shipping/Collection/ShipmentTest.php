<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Shipping\Collection;

/**
 * Integration tests for shipments reports collection which is used to obtain shipment reports by shipment date.
 */
class ShipmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Shipping\Collection\Shipment
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
            \Magento\Sales\Model\ResourceModel\Report\Shipping\Collection\Shipment::class
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
        $shipmentCreatedAt = $order->getShipmentsCollection()->getFirstItem()->getCreatedAt();
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTimeFactory::class)
            ->create();
        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone */
        $timezone = $this->objectManager->create(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $shipmentCreatedAt = $timezone->formatDateTime(
            $shipmentCreatedAt,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null,
            'yyyy-MM-dd'
        );
        $shipmentCreatedAtDate = $dateTime->date('Y-m-d', $shipmentCreatedAt);

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
