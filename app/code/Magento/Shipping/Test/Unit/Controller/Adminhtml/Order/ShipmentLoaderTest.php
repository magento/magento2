<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order;

use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $registryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shipmentRepositoryMock;

    /**
     * @var ShipmentDocumentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $documentFactoryMock;

    /**
     * @var ShipmentTrackCreationInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $trackFactoryMock;

    /**
     * @var ShipmentItemCreationInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->objectManagerMock = new ObjectManager($this);
        $this->shipmentRepositoryMock = $this->getMockBuilder(\Magento\Sales\Model\Order\ShipmentRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->trackFactoryMock = $this->getMockBuilder(ShipmentTrackCreationInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->itemFactoryMock = $this->getMockBuilder(ShipmentItemCreationInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->documentFactoryMock = $this->getMockBuilder(ShipmentDocumentFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
        $shipmentModelMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getForcedShipmentWithInvoice', 'getId', 'canShip'])
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
        $shipmentModelMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $trackMock = $this->getMockBuilder(ShipmentTrackCreationInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCarrierCode', 'setTrackNumber', 'setTitle'])
            ->getMockForAbstractClass();
        $this->trackFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($trackMock);
        $shipmentModelMock->expects($this->any())
            ->method('addTrack')
            ->with($this->equalTo($trackMock))
            ->willReturnSelf();
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
