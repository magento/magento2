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
class ShipmentLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentRepositoryMock;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;

    /**
     * @var ShipmentDocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentFactoryMock;

    /**
     * @var ShipmentTrackCreationInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentTrackCreationFactoryMock;

    /**
     * @var ShipmentItemCreationInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentItemCreationFactoryMock;

    /**
     * @var array
     */
    private $data;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $loader;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerMock = new ObjectManager($this);
        $this->shipmentRepositoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\ShipmentRepository')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentFactory = $this->getMockBuilder('Magento\Sales\Model\Order\ShipmentFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->trackFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\TrackFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->orderRepository = $this->getMockBuilder('Magento\Sales\Api\OrderRepositoryInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->documentFactoryMock = $this->getMockBuilder(ShipmentDocumentFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentTrackCreationFactoryMock = $this->getMockBuilder(ShipmentTrackCreationInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->shipmentItemCreationFactoryMock = $this->getMockBuilder(ShipmentItemCreationInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->data = [
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
                'shipmentFactory' => $this->shipmentFactory,
                'trackFactory' => $this->trackFactoryMock,
                'orderRepository' => $this->orderRepository,
                'data' => $this->data,
                'documentFactory' => $this->documentFactoryMock,
                'shipmentTrackCreationFactory' => $this->shipmentTrackCreationFactoryMock,
                'shipmentItemCreationFactory' => $this->shipmentItemCreationFactoryMock,
            ]
        );
    }

    public function testLoadShipmentId()
    {
        $shipmentModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
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
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['getForcedShipmentWithInvoice', 'getId', 'canShip'])
            ->getMock();
        $this->orderRepository->expects($this->once())
            ->method('get')
            ->will($this->returnValue($orderMock));

        $orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->loader->getOrderId()));
        $orderMock->expects($this->any())
            ->method('getForcedShipmentWithInvoice')
            ->will($this->returnValue(false));
        $orderMock->expects($this->once())
            ->method('canShip')
            ->will($this->returnValue(true));
        $shipmentModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $trackMock = $this->getMockBuilder(ShipmentTrackCreationInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCarrierCode', 'setTrackNumber', 'setTitle'])
            ->getMockForAbstractClass();

        $trackMock->expects($this->exactly(count($this->data['tracking'])))->method('setCarrierCode')->willReturnSelf();
        $trackMock->expects($this->exactly(count($this->data['tracking'])))->method('setTrackNumber')->willReturnSelf();
        $trackMock->expects($this->exactly(count($this->data['tracking'])))->method('setTitle')->willReturnSelf();

        $this->shipmentTrackCreationFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($trackMock);
        $shipmentModelMock->expects($this->any())
            ->method('addTrack')
            ->with($this->equalTo($trackMock))
            ->will($this->returnSelf());
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('current_shipment', $shipmentModelMock);

        $shipmentItemMock = $this->getMockBuilder(ShipmentItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrderItemId', 'setQty'])
            ->getMockForAbstractClass();

        $shipmentItemMock->expects($this->exactly(count($this->data['shipment']['items'])))
            ->method('setOrderItemId')->willReturnSelf();
        $shipmentItemMock->expects($this->exactly(count($this->data['shipment']['items'])))
            ->method('setQty')->willReturnSelf();
        $this->shipmentItemCreationFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($shipmentItemMock));

        $this->documentFactoryMock->expects($this->once())->method('create')->willReturn($shipmentModelMock);

        $this->assertEquals($shipmentModelMock, $this->loader->load());
    }
}
