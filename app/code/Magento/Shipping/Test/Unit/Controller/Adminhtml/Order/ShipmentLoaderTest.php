<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order;

/**
 * Class ShipmentLoaderTest
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order
 */
class ShipmentLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $loader;

    protected function setUp()
    {
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

        $data = [
            'order_id' => 100032,
            'shipment_id' => 1000065,
            'shipment' => ['items' => [1 => 1, 2 => 2]],
            'tracking' => [
                ['number' => 'jds0395'],
                ['number' => 'lsk984g'],
            ],
        ];

        $this->loader = new \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader(
            $this->messageManagerMock,
            $this->registryMock,
            $this->shipmentRepositoryMock,
            $this->shipmentFactory,
            $this->trackFactoryMock,
            $this->orderRepository,
            $data
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
        $this->shipmentFactory->expects($this->once())
            ->method('create')
            ->with($orderMock, $this->loader->getShipment()['items'])
            ->willReturn($shipmentModelMock);
        $trackMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\Track')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->trackFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($trackMock));
        $trackMock->expects($this->any())
            ->method('addData')
            ->will(
                $this->returnValueMap(
                    [
                        [$this->loader->getTracking()[0], $trackMock],
                        [$this->loader->getTracking()[1], $trackMock],
                    ]
                )
            );
        $shipmentModelMock->expects($this->any())
            ->method('addTrack')
            ->with($this->equalTo($trackMock))
            ->will($this->returnSelf());
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('current_shipment', $shipmentModelMock);

        $this->assertEquals($shipmentModelMock, $this->loader->load());
    }
}
