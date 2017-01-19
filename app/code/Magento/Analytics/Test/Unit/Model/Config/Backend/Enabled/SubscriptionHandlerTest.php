<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
     * @var Value|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueMock;

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

        $this->configValueMock = $this->getMockBuilder(Value::class)
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
     * @param int|null $value null means that $value was not changed
     * @param bool $isTokenExist
     * 
     * @dataProvider processDataProvider
     */
    public function testProcess($value, $isTokenExist)
    {
        $this->configValueMock
            ->expects($this->once())
            ->method('isValueChanged')
            ->willReturn(is_int($value));
        $this->tokenMock
            ->expects(is_int($value) ? $this->once() : $this->never())
            ->method('isTokenExist')
            ->willReturn($isTokenExist);
        if (is_int($value) && !$isTokenExist) {
            $this->configValueMock
                ->expects($this->once())
                ->method('getData')
                ->with('value')
                ->willReturn($value);

            if ($value === 1) {
                $this->addProcessWithEnabledTrueAsserts();
            } elseif ($value === 0) {
                $this->addProcessWithEnabledFalseAsserts();
            }
        }
        $this->assertTrue(
            $this->subscriptionHandler->process($this->configValueMock)
        );
    }

    /**
     * Add assertions for method process in case when new config value equals 1.
     *
     * @return void
     */
    private function addProcessWithEnabledTrueAsserts()
    {
        $this->configWriterMock
            ->expects($this->once())
            ->with(SubscriptionHandler::CRON_STRING_PATH, "0 * * * *")
            ->method('save');
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

    /**
     * Add assertions for method process in case when new config value equals 0.
     *
     * @return void
     */
    private function addProcessWithEnabledFalseAsserts()
    {
        $this->flagManagerMock
            ->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(true);
    }

    /**
     * Data provider for process test.
     *
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [null, true],
            [null, false],
            [0, true],
            [1, true],
            [0, false],
        ];
    }
}
