<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\CreateLabel;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateLabelTest extends TestCase
{
    /**
     * @var ShipmentLoader|MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var Shipment|MockObject
     */
    protected $shipmentMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Manager|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var LabelGenerator|MockObject
     */
    protected $labelGenerator;

    /**
     * @var CreateLabel
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->shipmentLoaderMock = $this->getMockBuilder(ShipmentLoader::class)
            ->addMethods(['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', '__wakeup'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentMock = $this->createPartialMock(
            Shipment::class,
            ['__wakeup', 'save']
        );
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['representJson', '__wakeup']
        );
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->messageManagerMock = $this->getMockBuilder(Manager::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['addSuccess', 'addError'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->labelGenerator = $this->getMockBuilder(LabelGenerator::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getRequest', 'getResponse', 'getMessageManager', 'getActionFlag', 'getObjectManager'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->loadShipment();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->controller = new CreateLabel(
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
            ->willThrowException(new LocalizedException(__('message')));
        $this->responseMock->expects($this->once())->method('representJson');

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (exception save shipment)
     */
    public function testExecuteSaveException()
    {
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->labelGenerator->expects($this->once())
            ->method('create')
            ->with($this->shipmentMock, $this->requestMock)
            ->willReturn(true);
        $this->shipmentMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $loggerMock->expects($this->once())->method('critical');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(LoggerInterface::class)
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
                new LocalizedException(__('message'))
            );
        $this->responseMock->expects($this->once())->method('representJson');

        $this->assertNull($this->controller->execute());
    }
}
