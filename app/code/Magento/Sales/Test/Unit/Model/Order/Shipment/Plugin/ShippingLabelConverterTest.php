<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment\Plugin;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Plugin\ShippingLabelConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for plugin to convert shipping label from blob to base64encoded string
 */
class ShippingLabelConverterTest extends TestCase
{
    /**
     * @var ShippingLabelConverter
     */
    private $model;

    /**
     * @var ShipmentInterface|MockObject
     */
    private $shipmentMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = new ShippingLabelConverter();

        $shippingLabel = 'shipping_label_test';
        $shippingLabelEncoded = base64_encode('shipping_label_test');
        $this->shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentMock->expects($this->exactly(2))->method('getShippingLabel')->willReturn($shippingLabel);
        $this->shipmentMock->expects($this->once())
            ->method('setShippingLabel')
            ->with($shippingLabelEncoded)
            ->willReturnSelf();
    }

    /**
     * @covers \Magento\Sales\Plugin\ShippingLabelConverter::afterGet()
     */
    public function testAfterGet()
    {
        $this->model->afterGet(
            $this->getMockBuilder(ShipmentRepositoryInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass(),
            $this->shipmentMock
        );
    }

    /**
     * @covers \Magento\Sales\Plugin\ShippingLabelConverter::afterGetList()
     */
    public function testAfterGetList()
    {
        $searchResultMock = $this->getMockBuilder(ShipmentSearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResultMock->expects($this->once())->method('getItems')->willReturn([$this->shipmentMock]);

        $this->model->afterGetList(
            $this->getMockBuilder(ShipmentRepositoryInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass(),
            $searchResultMock
        );
    }
}
