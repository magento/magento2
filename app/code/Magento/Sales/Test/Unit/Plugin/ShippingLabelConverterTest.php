<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Plugin\ShippingLabelConverter;

/**
 * Unit test for plugin to convert shipping label from blob to base64encoded string.
 */
class ShippingLabelConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingLabelConverter
     */
    private $model;

    /**
     * @var ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentMock;

    /**
     * @var ShipmentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->model = new ShippingLabelConverter();

        $shippingLabel = 'shipping_label_test';
        $shippingLabelEncoded = base64_encode('shipping_label_test');
        $this->shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->shipmentMock->expects($this->exactly(2))->method('getShippingLabel')->willReturn($shippingLabel);
        $this->shipmentMock->expects($this->once())
            ->method('setShippingLabel')
            ->with($shippingLabelEncoded)
            ->willReturnSelf();
        $this->shipmentRepositoryMock = $this->getMockBuilder(ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @covers \Magento\Sales\Plugin\ShippingLabelConverter::afterGet()
     */
    public function testAfterGet()
    {
        $this->model->afterGet(
            $this->shipmentRepositoryMock,
            $this->shipmentMock
        );
    }

    /**
     * @covers \Magento\Sales\Plugin\ShippingLabelConverter::afterGetList()
     */
    public function testAfterGetList()
    {
        $searchResultMock = $this->getMockBuilder(ShipmentSearchResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResultMock->expects($this->once())->method('getItems')->willReturn([$this->shipmentMock]);

        $this->model->afterGetList(
            $this->shipmentRepositoryMock,
            $searchResultMock
        );
    }
}
