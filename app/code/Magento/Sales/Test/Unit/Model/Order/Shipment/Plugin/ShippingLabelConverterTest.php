<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment\Plugin;

/**
 * Unit test for plugin to convert shipping label from blob to base64encoded string
 */
class ShippingLabelConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Plugin\ShippingLabelConverter
     */
    private $model;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shipmentMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = new \Magento\Sales\Plugin\ShippingLabelConverter();

        $shippingLabel = 'shipping_label_test';
        $shippingLabelEncoded = base64_encode('shipping_label_test');
        $this->shipmentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\ShipmentInterface::class)
            ->disableOriginalConstructor()->getMock();
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
            $this->getMockBuilder(\Magento\Sales\Api\ShipmentRepositoryInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $this->shipmentMock
        );
    }

    /**
     * @covers \Magento\Sales\Plugin\ShippingLabelConverter::afterGetList()
     */
    public function testAfterGetList()
    {
        $searchResultMock = $this->getMockBuilder(\Magento\Sales\Api\Data\ShipmentSearchResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResultMock->expects($this->once())->method('getItems')->willReturn([$this->shipmentMock]);

        $this->model->afterGetList(
            $this->getMockBuilder(\Magento\Sales\Api\ShipmentRepositoryInterface::class)
                ->disableOriginalConstructor()->getMock(),
            $searchResultMock
        );
    }
}
