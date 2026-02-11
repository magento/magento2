<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateLabelTest extends TestCase
{
    use MockCreationTrait;
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->shipmentLoaderMock = $this->createPartialMockWithReflection(
            ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', '__wakeup', 'load']
        );
        $this->shipmentMock = $this->createPartialMock(
            Shipment::class,
            ['__wakeup', 'save']
        );
        $this->requestMock = $this->createPartialMockWithReflection(
            Http::class,
            ['__wakeup', 'getParam']
        );
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['representJson', '__wakeup']
        );
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->messageManagerMock = $this->createPartialMockWithReflection(
            Manager::class,
            ['__wakeup', 'addSuccess', 'addError']
        );
        $this->labelGenerator = $this->createPartialMockWithReflection(
            LabelGenerator::class,
            ['__wakeup', 'create']
        );

        $contextMock = $this->createPartialMockWithReflection(
            Context::class,
            ['__wakeup', 'getRequest', 'getResponse', 'getMessageManager', 'getActionFlag', 'getObjectManager']
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
    protected function loadShipment(): void
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['order_id'] => $orderId,
                ['shipment_id'] => $shipmentId,
                ['shipment'] => $shipment,
                ['tracking'] => $tracking
            });
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
     *
     * @return void
     */
    public function testExecute(): void
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
     *
     * @return void
     */
    public function testExecuteLoadException(): void
    {
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willThrowException(new LocalizedException(__('message')));
        $this->responseMock->expects($this->once())->method('representJson');

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (exception save shipment)
     *
     * @return void
     */
    public function testExecuteSaveException(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);

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
     *
     * @return void
     */
    public function testExecuteLabelGenerateFail(): void
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
