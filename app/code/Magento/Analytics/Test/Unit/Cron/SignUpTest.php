<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Cron\SignUp;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SignUpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Connector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectorMock;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var SignUp
     */
    private $signUp;

    protected function setUp()
    {
        $this->connectorMock =  $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock =  $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagManagerMock =  $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reinitableConfigMock = $this->getMockBuilder(ReinitableConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $attemptsCount -= 1;
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
