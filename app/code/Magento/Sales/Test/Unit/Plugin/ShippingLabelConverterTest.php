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
     * @var ShipmentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->model = new ShippingLabelConverter();
        $this->shipmentRepositoryMock = $this->getMockBuilder(ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @covers \Magento\Sales\Plugin\ShippingLabelConverter::afterGet()
     * @param ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     * @return void
     * @dataProvider shipmentDataProvider
     */
    public function testAfterGet($shipmentMock)
    {
        $this->model->afterGet(
            $this->shipmentRepositoryMock,
            $shipmentMock
        );
    }

    /**
     * @covers \Magento\Sales\Plugin\ShippingLabelConverter::afterGetList()
     * @param ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     * @return void
     * @dataProvider shipmentDataProvider
     */
    public function testAfterGetList($shipmentMock)
    {
        $searchResultMock = $this->getMockBuilder(ShipmentSearchResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResultMock->expects($this->once())->method('getItems')->willReturn([$shipmentMock]);

        $this->model->afterGetList(
            $this->shipmentRepositoryMock,
            $searchResultMock
        );
    }

    /**
     * @return array
     */
    public function shipmentDataProvider()
    {
        return [
            ['shipmentMock' => $this->getShipmentMockWithLabel()],
            ['shipmentMock' => $this->getShipmentMockWithOutLabel()],
        ];
    }

    /**
     * @return ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getShipmentMockWithLabel()
    {
        $shippingLabel = 'shipping_label_test';
        $shippingLabelEncoded = base64_encode('shipping_label_test');
        $shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()->getMock();
        $shipmentMock->expects($this->exactly(2))->method('getShippingLabel')->willReturn($shippingLabel);
        $shipmentMock->expects($this->once())
            ->method('setShippingLabel')
            ->with($shippingLabelEncoded)
            ->willReturnSelf();

        return $shipmentMock;
    }

    /**
     * @return ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getShipmentMockWithOutLabel()
    {
        $shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()->getMock();
        $shipmentMock->expects($this->once())->method('getShippingLabel')->willReturn(null);
        $shipmentMock->expects($this->never())->method('setShippingLabel');

        return $shipmentMock;
    }
}
