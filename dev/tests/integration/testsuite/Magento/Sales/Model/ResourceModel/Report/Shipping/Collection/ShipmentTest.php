<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Shipping\Collection;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\Item;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection as ShipmentGridCollection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for shipments reports collection which is used to obtain shipment reports by shipment date.
 */
class ShipmentTest extends TestCase
{
    /**
     * @var Shipment
     */
    private $collection;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->collection = $this->objectManager->create(
            Shipment::class
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
        /** @var DateTime $dateTime */
        $dateTime = $this->objectManager->create(DateTimeFactory::class)
            ->create();
        /** @var TimezoneInterface $timezone */
        $timezone = $this->objectManager->create(TimezoneInterface::class);
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
        /** @var Item $reportItem */
        foreach ($this->collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Checks that order_created_at field does not change after sales_shipment_grid row update
     *
     * @magentoDataFixture Magento/Sales/_files/order_shipping.php
     * @return void
     */
    public function testOrderShipmentGridOrderCreatedAt(): void
    {
        $incrementId = '100000001';
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId($incrementId);
        /** @var ShipmentGridCollection $grid */
        $grid = $this->objectManager->get(ShipmentGridCollection::class);
        $grid->getSelect()
            ->where('order_increment_id', $incrementId);
        $itemId = $grid->getFirstItem()
            ->getEntityId();
        $connection = $grid->getResource()
            ->getConnection();
        $tableName = $grid->getMainTable();
        $connection->update(
            $tableName,
            ['customer_name' => 'Test'],
            $connection->quoteInto('entity_id = ?', $itemId)
        );
        $updatedRow = $connection->select()
            ->where('entity_id = ?', $itemId)
            ->from($tableName, ['order_created_at']);
        $orderCreatedAt = $connection->fetchOne($updatedRow);

        $this->assertEquals($order->getCreatedAt(), $orderCreatedAt);
    }
}
