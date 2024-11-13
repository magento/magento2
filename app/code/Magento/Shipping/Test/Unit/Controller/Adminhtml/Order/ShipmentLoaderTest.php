<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order;

use Magento\Framework\Message\Manager;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentLoaderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerMock;

    /**
     * @var MockObject
     */
    private $registryMock;

    /**
     * @var MockObject
     */
    private $messageManagerMock;

    /**
     * @var ShipmentRepository|MockObject
     */
    private $shipmentRepositoryMock;

    /**
     * @var ShipmentDocumentFactory|MockObject
     */
    private $documentFactoryMock;

    /**
     * @var ShipmentTrackCreationInterfaceFactory|MockObject
     */
    private $trackFactoryMock;

    /**
     * @var ShipmentItemCreationInterfaceFactory|MockObject
     */
    private $itemFactoryMock;

    /**
     * @var MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ShipmentLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->objectManagerMock = new ObjectManager($this);
        $this->shipmentRepositoryMock = $this->getMockBuilder(ShipmentRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->trackFactoryMock = $this->getMockBuilder(ShipmentTrackCreationInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->itemFactoryMock = $this->getMockBuilder(ShipmentItemCreationInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->documentFactoryMock = $this->getMockBuilder(ShipmentDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $data = [
            'order_id' => 100032,
            'shipment_id' => 1000065,
            'shipment' => ['items' => [1 => 1, 2 => 2]],
            'tracking' => [
                ['number' => 'jds0395', 'title' => 'DHL', 'carrier_code' => 'dhl'],
                ['number' => 'lsk984g', 'title' => 'UPS', 'carrier_code' => 'ups'],
            ],
        ];

        $this->loader = $this->objectManagerMock->getObject(
            ShipmentLoader::class,
            [
                'messageManager' => $this->messageManagerMock,
                'registry' => $this->registryMock,
                'shipmentRepository' => $this->shipmentRepositoryMock,
                'orderRepository' => $this->orderRepositoryMock,
                'documentFactory' => $this->documentFactoryMock,
                'trackFactory' => $this->trackFactoryMock,
                'itemFactory' => $this->itemFactoryMock,
                'data' => $data
            ]
        );
    }

    public function testLoadShipmentId()
    {
        $shipmentModelMock = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->loader->getShipmentId())
            ->willReturn($shipmentModelMock);
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('current_shipment', $shipmentModelMock);
        $this->assertEquals($shipmentModelMock, $this->loader->load());
    }

    public function testLoadOrderId()
    {
        $this->loader->unsetData('shipment_id');
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getForcedShipmentWithInvoice', 'getId', 'canShip'])
            ->getMock();
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->loader->getOrderId());
        $orderMock->expects($this->any())
            ->method('getForcedShipmentWithInvoice')
            ->willReturn(false);
        $orderMock->expects($this->once())
            ->method('canShip')
            ->willReturn(true);
        $shipmentModelMock = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $trackMock = $this->getMockBuilder(ShipmentTrackCreationInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setCarrierCode', 'setTrackNumber', 'setTitle'])
            ->getMockForAbstractClass();
        $this->trackFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($trackMock);
        $shipmentModelMock->expects($this->any())
            ->method('addTrack')
            ->with($trackMock)->willReturnSelf();
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('current_shipment', $shipmentModelMock);
        $itemMock = $this->getMockBuilder(ShipmentItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);
        $this->documentFactoryMock->expects($this->once())->method('create')->willReturn($shipmentModelMock);

        $this->assertEquals($shipmentModelMock, $this->loader->load());
    }
}
