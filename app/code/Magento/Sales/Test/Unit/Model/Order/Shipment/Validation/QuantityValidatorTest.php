<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment\Validation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuantityValidatorTest extends TestCase
{
    /**
     * @var QuantityValidator
     */
    private $validator;

    /**
     * @var ShipmentInterface|MockObject
     */
    private $shipmentMock;

    /**
     * @var ShipmentItemInterface|MockObject
     */
    private $shipmentItemMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->getMock();
        $this->shipmentItemMock = $this->getMockBuilder(ShipmentItemInterface::class)
            ->getMock();
        $this->validator = $objectManagerHelper->getObject(QuantityValidator::class);
    }

    public function testValidateTrackWithoutOrderId()
    {
        $this->shipmentMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn(null);
        $this->assertEquals(
            [__('Order Id is required for shipment document')],
            $this->validator->validate($this->shipmentMock)
        );
    }

    public function testValidateTrackWithoutItems()
    {
        $this->shipmentMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn(1);
        $this->shipmentMock->expects($this->once())
            ->method('getItems')
            ->willReturn(null);
        $this->assertEquals(
            [__('You can\'t create a shipment without products.')],
            $this->validator->validate($this->shipmentMock)
        );
    }
}
