<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SubscriptionHandlerTest
 */
class SubscriptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenMock;

    /**
     * @var NotificationTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationTimeMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var int
     */
    private $attemptsInitValue = 10;

    /**
     * @var SubscriptionHandler
     */
    private $subscriptionHandler;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenMock = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationTimeMock = $this->getMockBuilder(NotificationTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->subscriptionHandler = $this->objectManagerHelper->getObject(
            SubscriptionHandler::class,
            [
                'flagManager' => $this->flagManagerMock,
                'configWriter' => $this->configWriterMock,
                'attemptsInitValue' => $this->attemptsInitValue,
                'analyticsToken' => $this->tokenMock,
                'notificationTime' => $this->notificationTimeMock,
            ]
        );
    }

    /**
     * @param bool $isTokenExist
     *
     * @dataProvider processDataProvider
     */
    public function testProcessEnabled($isTokenExist)
    {
        $this->tokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->willReturn($isTokenExist);
        if (!$isTokenExist) {
            $this->configWriterMock
                ->expects($this->once())
                ->method('save')
                ->with(SubscriptionHandler::CRON_STRING_PATH, "0 * * * *");
            $this->flagManagerMock
                ->expects($this->once())
                ->method('saveFlag')
                ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue)
                ->willReturn(true);
            $this->notificationTimeMock
                ->expects($this->once())
                ->method('unsetLastTimeNotificationValue')
                ->willReturn(true);
        }
        $this->assertTrue(
            $this->subscriptionHandler->processEnabled()
        );
    }

    /**
     * @param bool $isTokenExist
     *
     * @dataProvider processDataProvider
     */
    public function testProcessDisabled($isTokenExist)
    {
        $this->configWriterMock
            ->expects($this->once())
            ->method('delete')
            ->with(CollectionTime::CRON_SCHEDULE_PATH);
        $this->tokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->willReturn($isTokenExist);
        if (!$isTokenExist) {
            $this->flagManagerMock
                ->expects($this->once())
                ->method('deleteFlag')
                ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
                ->willReturn(true);
        }
        $this->assertTrue(
            $this->subscriptionHandler->processDisabled()
        );
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'Token exist' => [true],
            'Token doesn\'t exist' => [false],
        ];
    }
}
