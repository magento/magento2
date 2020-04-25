<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment\Validation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Model\Order\Shipment\Validation\TrackValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TrackValidatorTest extends TestCase
{
    /**
     * @var TrackValidator
     */
    private $validator;

    /**
     * @var ShipmentInterface|MockObject
     */
    private $shipmentMock;

    /**
     * @var ShipmentTrackInterface|MockObject
     */
    private $shipmentTrackMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->getMockForAbstractClass();
        $this->shipmentTrackMock = $this->getMockBuilder(ShipmentTrackInterface::class)
            ->getMockForAbstractClass();
        $this->validator = $objectManagerHelper->getObject(TrackValidator::class);
    }

    public function testValidateTrackWithNumber()
    {
        $this->shipmentTrackMock->expects($this->once())
            ->method('getTrackNumber')
            ->willReturn('12345');
        $this->shipmentMock->expects($this->exactly(2))
            ->method('getTracks')
            ->willReturn([$this->shipmentTrackMock]);
        $this->assertEquals([], $this->validator->validate($this->shipmentMock));
    }

    public function testValidateTrackWithoutNumber()
    {
        $this->shipmentTrackMock->expects($this->once())
            ->method('getTrackNumber')
            ->willReturn(null);
        $this->shipmentMock->expects($this->exactly(2))
            ->method('getTracks')
            ->willReturn([$this->shipmentTrackMock]);
        $this->assertEquals([__('Please enter a tracking number.')], $this->validator->validate($this->shipmentMock));
    }

    public function testValidateTrackWithEmptyTracks()
    {
        $this->shipmentTrackMock->expects($this->never())
            ->method('getTrackNumber');
        $this->shipmentMock->expects($this->once())
            ->method('getTracks')
            ->willReturn([]);
        $this->assertEquals([], $this->validator->validate($this->shipmentMock));
    }
}
