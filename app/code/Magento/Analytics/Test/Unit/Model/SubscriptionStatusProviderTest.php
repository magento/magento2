<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Config\App\Config\Type\System;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SubscriptionStatusProviderTest.
 */
class SubscriptionStatusProviderTest extends \PHPUnit_Framework_TestCase
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
     * @param bool $isSubscriptionEnabled
     * @param bool $hasToken
     * @param int $attempts
     * @param string $expectedStatus
     */
    public function testGetStatus($isSubscriptionEnabled, $hasToken, $attempts, $expectedStatus)
    {
        $this->analyticsTokenMock->expects($this->exactly($attempts))
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
            'TestWithEnabledStatus' => [true, true, 1, "Enabled"],
            'TestWithPendingStatus' => [true, false, 1, "Pending"],
            'TestWithDisabledStatus' => [false, false, 0, "Disabled"],
            'TestWithDisabledStatus2' => [false, true, 0,  "Disabled"],
        ];
    }
}
