<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Config\App\Config\Type\System;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SubscriptionTest.
 */
class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var System|\PHPUnit_Framework_MockObject_MockObject
     */
    private $systemConfigMock;
    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SubscriptionStatusProvider
     */
    private $statusProvider;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->systemConfigMock = $this->getMockBuilder(System::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->analyticsTokenMock = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->statusProvider = $this->objectManagerHelper->getObject(
            SubscriptionStatusProvider::class,
            [
                'systemConfig' => $this->systemConfigMock,
                'analyticsToken' => $this->analyticsTokenMock
            ]
        );
    }

    /**
     * @dataProvider statusDataProvider
     *
     * @param boolean $isSubscriptionEnabled
     * @param boolean $hasToken
     * @param string $expectedStatus
     *
     * @return void
     */
    public function testGetStatus($isSubscriptionEnabled, $hasToken, $expectedStatus)
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn($hasToken);
        $this->systemConfigMock->expects($this->once())
            ->method('get')
            ->with('default/analytics/subscription/enabled')
            ->willReturn($isSubscriptionEnabled);
        $this->assertEquals($expectedStatus, $this->statusProvider->getStatus());
    }

    /**
     * @return array
     */
    public function statusDataProvider()
    {
        return [
            'TestWithEnabledStatus' => [true, true, "Enabled"],
            'TestWithPendingStatus' => [true, false, "Pending"],
            'TestWithDisabledStatus' => [false, false, "Disabled"],
            'TestWithDisabledStatus2' => [false, true, "Disabled"],
        ];
    }
}
