<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Shipment\Validation;

use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

/**
 * Class QuantityValidatorTest
 */
class QuantityValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuantityValidator
     */
    private $validator;

    /**
     * @var ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentMock;

    /**
     * @var ShipmentItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentItemMock;

    protected function setUp()
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
