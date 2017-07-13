<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SubscriptionStatusProviderTest.
 */
class SubscriptionStatusProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

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
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

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
                'scopeConfig' => $this->scopeConfigMock,
                'analyticsToken' => $this->analyticsTokenMock,
                'flagManager' => $this->flagManagerMock,
            ]
        );
    }

    /**
     * @param array $flagManagerData
     * @dataProvider getStatusShouldBeFailedDataProvider
     */
    public function testGetStatusShouldBeFailed(array $flagManagerData)
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('analytics/subscription/enabled')
            ->willReturn(true);

        $this->expectFlagManagerReturn($flagManagerData);
        $this->assertEquals(SubscriptionStatusProvider::FAILED, $this->statusProvider->getStatus());
    }

    /**
     * @return array
     */
    public function getStatusShouldBeFailedDataProvider()
    {
        return [
            'Subscription update doesn\'t active' => [
                'Flag Manager data mapping' => [
                    [SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE, null],
                    [SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, null]
                ],
            ],
            'Subscription update is active' => [
                'Flag Manager data mapping' => [
                    [SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE, 'http://store.com'],
                    [SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, null]
                ],
            ],
        ];
    }

    /**
     * @param array $flagManagerData
     * @param bool $isTokenExist
     * @dataProvider getStatusShouldBePendingDataProvider
     */
    public function testGetStatusShouldBePending(array $flagManagerData, bool $isTokenExist)
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn($isTokenExist);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('analytics/subscription/enabled')
            ->willReturn(true);

        $this->expectFlagManagerReturn($flagManagerData);
        $this->assertEquals(SubscriptionStatusProvider::PENDING, $this->statusProvider->getStatus());
    }

    /**
     * @return array
     */
    public function getStatusShouldBePendingDataProvider()
    {
        return [
            'Subscription update doesn\'t active and the token does not exist' => [
                'Flag Manager data mapping' => [
                    [SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE, null],
                    [SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, 45]
                ],
                'isTokenExist' => false,
            ],
            'Subscription update is active and the token does not exist' => [
                'Flag Manager data mapping' => [
                    [SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE, 'http://store.com'],
                    [SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, 45]
                ],
                'isTokenExist' => false,
            ],
            'Subscription update is active and token exist' => [
                'Flag Manager data mapping' => [
                    [SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE, 'http://store.com'],
                    [SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, null]
                ],
                'isTokenExist' => true,
            ],
        ];
    }

    public function testGetStatusShouldBeEnabled()
    {
        $this->flagManagerMock
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE)
            ->willReturn(null);
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('analytics/subscription/enabled')
            ->willReturn(true);
        $this->assertEquals(SubscriptionStatusProvider::ENABLED, $this->statusProvider->getStatus());
    }

    public function testGetStatusShouldBeDisabled()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('analytics/subscription/enabled')
            ->willReturn(false);
        $this->assertEquals(SubscriptionStatusProvider::DISABLED, $this->statusProvider->getStatus());
    }

    /**
     * @param array $mapping
     */
    private function expectFlagManagerReturn(array $mapping)
    {
        $this->flagManagerMock
            ->method('getFlagData')
            ->willReturnMap($mapping);
    }
}
