<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

/**
 * Class CreateLabelTest
 */
class CreateLabelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $labelGenerator;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\CreateLabel
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->shipmentLoaderMock = $this->createPartialMock(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load', '__wakeup']
        );
        $this->shipmentMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['__wakeup', 'save']
        );
        $this->requestMock = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getParam', '__wakeup']
        );
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['representJson', '__wakeup']
        );
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->messageManagerMock = $this->createPartialMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccess', 'addError', '__wakeup']
        );
        $this->labelGenerator = $this->createPartialMock(
            \Magento\Shipping\Model\Shipping\LabelGenerator::class,
            ['create', '__wakeup']
        );

        $contextMock = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getMessageManager', 'getActionFlag', 'getObjectManager', '__wakeup']
        );

        $this->loadShipment();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

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
            ->willReturn($orderId);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipment_id')
            ->willReturn($shipmentId);
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment')
            ->willReturn($shipment);
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('tracking')
            ->willReturn($tracking);
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
            ->willReturn($this->shipmentMock);
        $this->labelGenerator->expects($this->once())
            ->method('create')
            ->with($this->shipmentMock, $this->requestMock)
            ->willReturn(true);
        $this->shipmentMock->expects($this->once())->method('save')->willReturnSelf();
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
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->labelGenerator->expects($this->once())
            ->method('create')
            ->with($this->shipmentMock, $this->requestMock)
            ->willReturn(true);
        $this->shipmentMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $loggerMock->expects($this->once())->method('critical');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($loggerMock);
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
            ->willReturn($this->shipmentMock);
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
