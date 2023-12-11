<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Cron\SignUp;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SignUpTest extends TestCase
{
    /**
     * @var Connector|MockObject
     */
    private $connectorMock;

    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @var FlagManager|MockObject
     */
    private $flagManagerMock;

    /**
     * @var ReinitableConfigInterface|MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var SignUp
     */
    private $signUp;

    protected function setUp(): void
    {
        $this->connectorMock = $this->createMock(Connector::class);
        $this->configWriterMock = $this->getMockForAbstractClass(WriterInterface::class);
        $this->flagManagerMock = $this->createMock(FlagManager::class);
        $this->reinitableConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);

        $this->signUp = new SignUp(
            $this->connectorMock,
            $this->configWriterMock,
            $this->flagManagerMock,
            $this->reinitableConfigMock
        );
    }

    public function testExecute()
    {
        $attemptsCount = 10;

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($attemptsCount);

        $attemptsCount--;
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
        $this->connectorMock->expects($this->once())
            ->method('execute')
            ->with('signUp')
            ->willReturn(true);
        $this->addDeleteAnalyticsCronExprAsserts();
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        $this->assertTrue($this->signUp->execute());
    }

    public function testExecuteFlagNotExist()
    {
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(null);
        $this->addDeleteAnalyticsCronExprAsserts();
        $this->assertFalse($this->signUp->execute());
    }

    public function testExecuteZeroAttempts()
    {
        $attemptsCount = 0;
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($attemptsCount);
        $this->addDeleteAnalyticsCronExprAsserts();
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        $this->assertFalse($this->signUp->execute());
    }

    /**
     * Add assertions for method deleteAnalyticsCronExpr.
     *
     * @return void
     */
    private function addDeleteAnalyticsCronExprAsserts()
    {
        $this->configWriterMock
            ->expects($this->once())
            ->method('delete')
            ->with(SubscriptionHandler::CRON_STRING_PATH)
            ->willReturn(true);
        $this->reinitableConfigMock
            ->expects($this->once())
            ->method('reinit')
            ->willReturnSelf();
    }
}
