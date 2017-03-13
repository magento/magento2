<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\FlagManager;
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
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

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

        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->statusProvider = $this->objectManagerHelper->getObject(
            SubscriptionStatusProvider::class,
            [
                'systemConfig' => $this->systemConfigMock,
                'analyticsToken' => $this->analyticsTokenMock,
                'flagManager' => $this->flagManagerMock,
            ]
        );
    }

    /**
     * @return array
     */
    public function statusDataProvider()
    {
        return [
            'TestWithEnabledStatus' => [true, true, 1, "Enabled", 42],
            'TestWithPendingStatus' => [true, false, 1, "Pending", 42],
            'TestWithDisabledStatus' => [false, false, 0, "Disabled", 42],
            'TestWithDisabledStatus2' => [false, true, 0,  "Disabled", 42],
            'TestWithFailedStatus' => [true, false, 1, "Failed", null],
        ];
    }

    /**
     * @dataProvider statusDataProvider
     *
     * @param bool $isSubscriptionEnabled
     * @param bool $hasToken
     * @param int $tokenExistsCallCount
     * @param string $expectedStatus
     * @param int|null $reverseCounter
     */
    public function testGetStatus(
        $isSubscriptionEnabled,
        $hasToken,
        $tokenExistsCallCount,
        $expectedStatus,
        $reverseCounter
    ) {
        $this->analyticsTokenMock->expects($this->exactly($tokenExistsCallCount))
            ->method('isTokenExist')
            ->willReturn($hasToken);
        $this->systemConfigMock->expects($this->once())
            ->method('get')
            ->with('default/analytics/subscription/enabled')
            ->willReturn($isSubscriptionEnabled);
        $this->flagManagerMock->expects($this->any())->method('getFlagData')
            ->willReturnMap(
                [
                    [SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $reverseCounter],
                ]
            );
        $this->assertEquals($expectedStatus, $this->statusProvider->getStatus());
    }
}
