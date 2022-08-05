<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubscriptionHandlerTest extends TestCase
{
    /**
     * @var FlagManager|MockObject
     */
    private $flagManagerMock;

    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @var AnalyticsToken|MockObject
     */
    private $tokenMock;

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

    protected function setUp(): void
    {
        $this->flagManagerMock = $this->createMock(FlagManager::class);

        $this->configWriterMock = $this->getMockForAbstractClass(WriterInterface::class);

        $this->tokenMock = $this->createMock(AnalyticsToken::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->subscriptionHandler = $this->objectManagerHelper->getObject(
            SubscriptionHandler::class,
            [
                'flagManager' => $this->flagManagerMock,
                'configWriter' => $this->configWriterMock,
                'attemptsInitValue' => $this->attemptsInitValue,
                'analyticsToken' => $this->tokenMock,
            ]
        );
    }

    public function testProcessEnabledTokenExist()
    {
        $this->tokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->configWriterMock
            ->expects($this->never())
            ->method('save');
        $this->flagManagerMock
            ->expects($this->never())
            ->method('saveFlag');
        $this->assertTrue(
            $this->subscriptionHandler->processEnabled()
        );
    }

    public function testProcessEnabledTokenDoesNotExist()
    {
        $this->tokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);
        $this->configWriterMock
            ->expects($this->once())
            ->method('save')
            ->with(SubscriptionHandler::CRON_STRING_PATH, "0 * * * *");
        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue)
            ->willReturn(true);
        $this->assertTrue(
            $this->subscriptionHandler->processEnabled()
        );
    }

    public function testProcessDisabledTokenDoesNotExist()
    {
        $this->configWriterMock
            ->expects($this->once())
            ->method('delete')
            ->with(CollectionTime::CRON_SCHEDULE_PATH);
        $this->tokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(true);
        $this->assertTrue(
            $this->subscriptionHandler->processDisabled()
        );
    }

    public function testProcessDisabledTokenExists()
    {
        $this->configWriterMock
            ->expects($this->once())
            ->method('delete')
            ->with(CollectionTime::CRON_SCHEDULE_PATH);
        $this->tokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->flagManagerMock
            ->expects($this->never())
            ->method('deleteFlag');
        $this->assertTrue(
            $this->subscriptionHandler->processDisabled()
        );
    }
}
