<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

/**
 * Class CreateLabelTest
 */
class CreateLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelGenerator;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\CreateLabel
     */
    protected $controller;

    protected function setUp()
    {
        $this->shipmentLoaderMock = $this->getMock(
            'Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader',
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load', '__wakeup'],
            [],
            '',
            false
        );
        $this->shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['__wakeup', 'save'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock(
            'Magento\Framework\App\Request\Http',
            ['getParam', '__wakeup'],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            ['representJson', '__wakeup'],
            [],
            '',
            false
        );
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->messageManagerMock = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess', 'addError', '__wakeup'],
            [],
            '',
            false
        );
        $this->labelGenerator = $this->getMock(
            'Magento\Shipping\Model\Shipping\LabelGenerator',
            ['create', '__wakeup'],
            [],
            '',
            false
        );

        $contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            ['getRequest', 'getResponse', 'getMessageManager', 'getActionFlag', 'getObjectManager', '__wakeup'],
            [],
            '',
            false
        );

        $this->loadShipment();
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\CreateLabel(
            $contextMock,
            $this->shipmentLoaderMock,
            $this->labelGenerator
        );
    }

    /**
     * Load shipment object
     *
     * @return void
     */
    protected function loadShipment()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipment_id')
            ->will($this->returnValue($shipmentId));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment')
            ->will($this->returnValue($shipment));
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('tracking')
            ->will($this->returnValue($tracking));
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setShipment')
            ->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setTracking')
            ->with($tracking);
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->labelGenerator->expects($this->once())
            ->method('create')
            ->with($this->shipmentMock, $this->requestMock)
            ->will($this->returnValue(true));
        $this->shipmentMock->expects($this->once())->method('save')->will($this->returnSelf());
        $this->messageManagerMock->expects($this->once())->method('addSuccess');
        $this->responseMock->expects($this->once())->method('representJson');

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (exception load shipment)
     */
    public function testExecuteLoadException()
    {
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('message')));
        $this->responseMock->expects($this->once())->method('representJson');

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (exception save shipment)
     */
    public function testExecuteSaveException()
    {
        $logerMock = $this->getMock('Psr\Log\LoggerInterface');

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->labelGenerator->expects($this->once())
            ->method('create')
            ->with($this->shipmentMock, $this->requestMock)
            ->will($this->returnValue(true));
        $this->shipmentMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $logerMock->expects($this->once())->method('critical');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->will($this->returnValue($logerMock));
        $this->responseMock->expects($this->once())->method('representJson');

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail generate label)
     */
    public function testExecuteLabelGenerateFail()
    {
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->labelGenerator->expects($this->once())
            ->method('create')
            ->with($this->shipmentMock, $this->requestMock)
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('message'))
            );
        $this->responseMock->expects($this->once())->method('representJson');

        $this->assertNull($this->controller->execute());
    }
}
