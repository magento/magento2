<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Service\V1\Action;

/**
 * Class ShipmentCreateTest
 */
class ShipmentCreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentCreate
     */
    protected $shipmentCreate;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    public function setUp()
    {
        $this->shipmentConverterMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\ShipmentConverter')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Magento\Framework\Logger')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->shipmentCreate = new ShipmentCreate(
            $this->shipmentConverterMock,
            $this->loggerMock
        );
    }

    public function testInvoke()
    {
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $orderMock->expects($this->any())
            ->method('setIsInProcess')
            ->with(true);
        $shipmentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $shipmentMock->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));
        $shipmentMock->expects($this->once())
            ->method('register');
        $shipmentMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));
        $shipmentDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentConverterMock->expects($this->once())
            ->method('getModel')
            ->with($shipmentDataObjectMock)
            ->will($this->returnValue($shipmentMock));
        $this->assertTrue($this->shipmentCreate->invoke($shipmentDataObjectMock));
    }

    public function testInvokeNoShipment()
    {
        $shipmentDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentConverterMock->expects($this->once())
            ->method('getModel')
            ->with($shipmentDataObjectMock)
            ->will($this->returnValue(false));
        $this->assertFalse($this->shipmentCreate->invoke($shipmentDataObjectMock));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An error has occurred during creating Shipment
     */
    public function testInvokeException()
    {
        $message = 'Can not save Shipment';
        $e = new \Exception($message);

        $shipmentDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerMock->expects($this->once())
            ->method('logException')
            ->with($e);
        $this->shipmentConverterMock->expects($this->once())
            ->method('getModel')
            ->with($shipmentDataObjectMock)
            ->will($this->throwException($e));
        $this->shipmentCreate->invoke($shipmentDataObjectMock);
    }
}
