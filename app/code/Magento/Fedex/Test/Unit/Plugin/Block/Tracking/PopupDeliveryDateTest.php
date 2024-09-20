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
 * Unit Test for \Magento\Fedex\Plugin\Block\Tracking\PopupDeliveryDate
 */
class PopupDeliveryDateTest extends TestCase
{
    public const STUB_CARRIER_CODE_NOT_FEDEX = 'not-fedex';
    public const STUB_DELIVERY_DATE = '2020-02-02';
    public const STUB_DELIVERY_TIME = '12:00';

    /**
     * @var MockObject|PopupDeliveryDate
     */
    private $plugin;

    /**
     * @var MockObject|Status $trackingStatusMock
     */
    private $trackingStatusMock;

    /**
     * @var MockObject|Popup $subjectMock
     */
    private $subjectMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->trackingStatusMock = $this->getStatusMock();
        $this->subjectMock = $this->getPopupMock();
        $this->subjectMock->expects($this->once())
            ->method('getTrackingInfo')
            ->willReturn([[$this->trackingStatusMock]]);

        $objectManagerHelper = new ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(PopupDeliveryDate::class);
    }

    /**
     * Test the method with Fedex carrier
     */
    public function testAfterFormatDeliveryDateTimeWithFedexCarrier()
    {
        $this->trackingStatusMock->expects($this::once())
            ->method('getCarrier')
            ->willReturn(Carrier::CODE);
        $this->subjectMock->expects($this->once())->method('formatDeliveryDate');

        $this->executeOriginalMethod();
    }

    /**
     * Test the method with Fedex carrier with timezone impact
     * @dataProvider getDates
     */
    public function testAfterFormatDeliveryDateTimeWithFedexCarrierWithTimezone(
        $date,
        $currentTimezone,
        $convertedTimezone,
        $expected
    ) {
        $this->trackingStatusMock->expects($this::once())
            ->method('getCarrier')
            ->willReturn(Carrier::CODE);

        $date = new \DateTime($date, new \DateTimeZone($currentTimezone));
        $date->setTimezone(new \DateTimeZone($convertedTimezone));
        $this->subjectMock->expects($this->once())->method('formatDeliveryDate')
        ->willReturn($date->format('Y-m-d'));

        $result = $this->executeOriginalMethodWithTimezone();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test the method with a different carrier
     */
    public function testAfterFormatDeliveryDateTimeWithOtherCarrier()
    {
        $this->trackingStatusMock->expects($this::once())
            ->method('getCarrier')
            ->willReturn(self::STUB_CARRIER_CODE_NOT_FEDEX);
        $this->subjectMock->expects($this->never())->method('formatDeliveryDate');

        $this->executeOriginalMethod();
    }

    /**
     * Returns Mock for \Magento\Shipping\Model\Tracking\Result\Status
     *
     * @return MockObject
     */
    private function getStatusMock(): MockObject
    {
        return $this->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCarrier'])
            ->getMock();
    }

    /**
     * Returns Mock for \Magento\Shipping\Block\Tracking\Popup
     *
     * @return MockObject
     */
    private function getPopupMock(): MockObject
    {
        return $this->getMockBuilder(Popup::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['formatDeliveryDate', 'getTrackingInfo'])
            ->getMock();
    }

    /**
     * Run plugin's original method
     */
    private function executeOriginalMethod()
    {
        $this->plugin->afterFormatDeliveryDateTime(
            $this->subjectMock,
            'Test Result',
            self::STUB_DELIVERY_DATE,
            self::STUB_DELIVERY_TIME
        );
    }

    /**
     * Run plugin's original method taking into account timezone
     */
    private function executeOriginalMethodWithTimezone()
    {
        return $this->plugin->afterFormatDeliveryDateTime(
            $this->subjectMock,
            'Test Result',
            self::STUB_DELIVERY_DATE,
            '00:00:00'
        );
    }

    /**
     * Data provider for testAfterFormatDeliveryDateTimeWithFedexCarrierWithTimezone
     *
     * @return array[]
     */
    public static function getDates(): array
    {
        return [
            'same day' => [
                'date' => '2024-01-07 06:00:00',
                'currentTimezone' => 'US/Eastern',
                'convertedTimezone' => 'America/Chicago',
                'expected' => '2024-01-07'
            ],
            'previous day' => [
                'date' => '2024-01-07 00:00:00',
                'currentTimezone' => 'US/Eastern',
                'convertedTimezone' => 'America/Chicago',
                'expected' => '2024-01-06'
            ]
        ];
    }
}
