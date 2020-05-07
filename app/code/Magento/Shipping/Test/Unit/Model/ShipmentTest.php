<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Shipping\Test\Unit\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\CommentFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    protected $orderRepository;

    /**
     * @var Shipment
     */
    protected $shipment;

    protected function setUp(): void
    {
        $this->orderRepository = $this->getMockForAbstractClass(OrderRepositoryInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = [
            'context' => $this->createMock(Context::class),
            'registry' => $this->createMock(Registry::class),
            'localeDate' => $this->createMock(
                TimezoneInterface::class
            ),
            'dateTime' => $this->createMock(DateTime::class),
            'orderRepository' => $this->orderRepository,
            'shipmentItemCollectionFactory' => $this->createMock(
                CollectionFactory::class
            ),
            'trackCollectionFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory::class
            ),
            'commentFactory' => $this->createMock(CommentFactory::class),
            'commentCollectionFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory::class
            ),
        ];
        $this->shipment = $objectManagerHelper->getObject(
            Shipment::class,
            $arguments
        );
    }

    public function testGetOrder()
    {
        $orderId = 100000041;
        $this->shipment->setOrderId($orderId);
        $entityName = 'shipment';
        $order = $this->createPartialMock(
            Order::class,
            ['load', 'setHistoryEntityName', '__wakeUp']
        );
        $this->shipment->setOrderId($orderId);
        $order->expects($this->atLeastOnce())
            ->method('setHistoryEntityName')
            ->with($entityName)->willReturnSelf();

        $this->orderRepository->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($order);

        $this->assertEquals($order, $this->shipment->getOrder());
    }

    public function testGetEntityType()
    {
        $this->assertEquals('shipment', $this->shipment->getEntityType());
    }
}
