<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SubscriptionStatusProviderTest.
 */
class SubscriptionStatusProviderTest extends \PHPUnit_Framework_TestCase
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

    public function testGetStatusShouldBeFailed()
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('analytics/subscription/enabled')
            ->willReturn(true);

        $this->expectFlagCounterReturn(null);
        $this->assertEquals(SubscriptionStatusProvider::FAILED, $this->statusProvider->getStatus());
    }

    public function testGetStatusShouldBePending()
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('analytics/subscription/enabled')
            ->willReturn(true);

        $this->expectFlagCounterReturn(45);
        $this->assertEquals(SubscriptionStatusProvider::PENDING, $this->statusProvider->getStatus());
    }

    public function testGetStatusShouldBeEnabled()
    {
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
     * @param null|int $value
     */
    private function expectFlagCounterReturn($value)
    {
        $this->flagManagerMock->expects($this->once())->method('getFlagData')
            ->willReturnMap(
                [
                    [SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $value],
                ]
            );
    }
}
