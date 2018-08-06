<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\ShipmentDocumentFactory;

use Magento\Framework\Reflection\ExtensionAttributesProcessor as AttributesProcessor;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentExtensionInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\ShipmentDocumentFactory\ExtensionAttributesProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for shipment document factory extension attributes processor.
 */
class ExtensionAttributesProcessorTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var ExtensionAttributesProcessor
     */
    private $extensionAttributesProcessor;

    /**
     * @var AttributesProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesProcessorMock;

    /**
     * @var ShipmentExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentExtensionFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->shipmentExtensionFactoryMock = $this->getMockBuilder(ShipmentExtensionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesProcessorMock = $this->getMockBuilder(AttributesProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesProcessor = new ExtensionAttributesProcessor(
            $this->shipmentExtensionFactoryMock,
            $this->extensionAttributesProcessorMock
        );
    }

    /**
     * Build and set extension attributes for shipment with shipment creation arguments.
     *
     * @return void
     */
    public function testExecuteWithParameter(): void
    {
        /** @var ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject $shipmentMock */
        $shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipmentMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $attributes = $this->getMockBuilder(ShipmentCreationArgumentsExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var ShipmentCreationArgumentsInterface|\PHPUnit_Framework_MockObject_MockObject $argumentsMock */
        $argumentsMock = $this->getMockBuilder(ShipmentCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $argumentsMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($attributes);

        $shipmentExtensionAttributes = $this->getMockBuilder(ShipmentExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setTestAttribute'])
            ->getMockForAbstractClass();
        $shipmentExtensionAttributes->expects($this->once())
            ->method('setTestAttribute')
            ->with('test_value')
            ->willReturnSelf();

        $this->shipmentExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($shipmentExtensionAttributes);

        $this->extensionAttributesProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($attributes, ShipmentCreationArgumentsExtensionInterface::class)
            ->willReturn(['test_attribute' => 'test_value']);

        $this->extensionAttributesProcessor->execute($shipmentMock, $argumentsMock);
    }

    /**
     * Build and set extension attributes for shipment without shipment creation arguments.
     *
     * @return void
     */
    public function testExecuteWithoutParameter(): void
    {
        /** @var ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject $shipmentMock */
        $shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipmentMock->expects($this->never())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $this->shipmentExtensionFactoryMock->expects($this->never())
            ->method('create');

        $this->extensionAttributesProcessorMock->expects($this->never())
            ->method('buildOutputDataArray');

        $this->extensionAttributesProcessor->execute($shipmentMock, null);
    }
}
