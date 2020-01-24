<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Fedex\Test\Unit\Plugin\Block\Tracking;

use Magento\Fedex\Model\Carrier;
use Magento\Fedex\Plugin\Block\Tracking\PopupDeliveryDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Shipping\Block\Tracking\Popup;
use Magento\Shipping\Model\Tracking\Result\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for @see \Magento\Fedex\Plugin\Block\Tracking\PopupDeliveryDate
 */
class PopupDeliveryDateTest extends TestCase
{
    /**
     * @var MockObject|PopupDeliveryDate
     */
    private $plugin;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(PopupDeliveryDate::class);
    }

    /**
     * Test the method with Fedex carrier
     */
    public function testAfterFormatDeliveryDateTimeWithFedexCarrier()
    {
        /** @var Status|MockObject $trackingStatusMock */
        $trackingStatusMock = $this->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCarrier'])
            ->getMock();
        $trackingStatusMock->expects($this::once())
            ->method('getCarrier')
            ->willReturn(Carrier::CODE);

        /** @var Popup|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(Popup::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatDeliveryDate', 'getTrackingInfo'])
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('getTrackingInfo')
            ->willReturn([[$trackingStatusMock]]);
        $subjectMock->expects($this->once())
            ->method('formatDeliveryDate');

        $this->plugin->afterFormatDeliveryDateTime($subjectMock, 'Test Result', '2020-02-02', '12:00');
    }

    /**
     * Test the method with a different carrier
     */
    public function testAfterFormatDeliveryDateTimeWithOtherCarrier()
    {
        /** @var Status|MockObject $trackingStatusMock */
        $trackingStatusMock = $this->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCarrier'])
            ->getMock();
        $trackingStatusMock->expects($this::once())
            ->method('getCarrier')
            ->willReturn('not-fedex');

        /** @var Popup|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(Popup::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatDeliveryDate', 'getTrackingInfo'])
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('getTrackingInfo')
            ->willReturn([[$trackingStatusMock]]);
        $subjectMock->expects($this->never())
            ->method('formatDeliveryDate');

        $this->plugin->afterFormatDeliveryDateTime($subjectMock, 'Test Result', '2020-02-02', '12:00');
    }
}
