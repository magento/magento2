<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Config\Backend\Baseurl;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubscriptionUpdateHandlerTest extends TestCase
{
    /**
     * @var AnalyticsToken|MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var FlagManager|MockObject
     */
    private $flagManagerMock;

    /**
     * @var ReinitableConfigInterface|MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SubscriptionUpdateHandler
     */
    private $subscriptionUpdateHandler;

    /**
     * @var int
     */
    private $attemptsInitValue = 48;

    /**
     * @var string
     */
    private $cronExpression = '0 * * * *';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->reinitableConfigMock = $this->getMockBuilder(ReinitableConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->analyticsTokenMock = $this->createMock(AnalyticsToken::class);

        $this->flagManagerMock = $this->createMock(FlagManager::class);

        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->subscriptionUpdateHandler = $this->objectManagerHelper->getObject(
            SubscriptionUpdateHandler::class,
            [
                'reinitableConfig' => $this->reinitableConfigMock,
                'analyticsToken' => $this->analyticsTokenMock,
                'flagManager' => $this->flagManagerMock,
                'configWriter' => $this->configWriterMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testTokenDoesNotExist()
    {
        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->with()
            ->willReturn(false);
        $this->flagManagerMock
            ->expects($this->never())
            ->method('saveFlag');
        $this->configWriterMock
            ->expects($this->never())
            ->method('save');
        $this->assertTrue($this->subscriptionUpdateHandler->processUrlUpdate('http://store.com'));
    }

    /**
     * @return void
     */
    public function testTokenAndPreviousBaseUrlExist()
    {
        $url = 'https://store.com';
        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->with()
            ->willReturn(true);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE)
            ->willReturn(true);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->willReturnCallback(function ($arg1, $arg2) use ($url) {
                if ($arg1 == SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE
                 && $arg2 == $this->attemptsInitValue) {
                    return true;
                } elseif ($arg1 == SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE && $arg2 == $url) {
                    return true;
                }
            });

        $this->configWriterMock
            ->expects($this->once())
            ->method('save')
            ->with(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH, $this->cronExpression);
        $this->reinitableConfigMock
            ->expects($this->once())
            ->method('reinit')
            ->with();
        $this->assertTrue($this->subscriptionUpdateHandler->processUrlUpdate($url));
    }

    /**
     * @return void
     */
    public function testTokenExistAndWithoutPreviousBaseUrl()
    {
        $url = 'https://store.com';
        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->with()
            ->willReturn(true);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE)
            ->willReturn(false);
        $this->flagManagerMock
            ->expects($this->exactly(2))
            ->method('saveFlag')
            ->willReturnCallback(function ($arg1, $arg2) use ($url) {
                if ($arg1 == SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE
                    && $arg2 == $this->attemptsInitValue) {
                    return true;
                } elseif ($arg1 == SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE && $arg2 == $url) {
                    return true;
                }
            });
        $this->configWriterMock
            ->expects($this->once())
            ->method('save')
            ->with(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH, $this->cronExpression);
        $this->reinitableConfigMock
            ->expects($this->once())
            ->method('reinit')
            ->with();
        $this->assertTrue($this->subscriptionUpdateHandler->processUrlUpdate($url));
    }
}
