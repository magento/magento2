<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Cron\Update;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\FlagManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
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
     * @var AnalyticsToken|MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var Update
     */
    private $update;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->connectorMock =  $this->createMock(Connector::class);
        $this->configWriterMock =  $this->getMockForAbstractClass(WriterInterface::class);
        $this->flagManagerMock =  $this->createMock(FlagManager::class);
        $this->reinitableConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);
        $this->analyticsTokenMock = $this->createMock(AnalyticsToken::class);

        $this->update = new Update(
            $this->connectorMock,
            $this->configWriterMock,
            $this->reinitableConfigMock,
            $this->flagManagerMock,
            $this->analyticsTokenMock
        );
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testExecuteWithoutToken()
    {
        $this->flagManagerMock
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(10);
        $this->connectorMock
            ->expects($this->never())
            ->method('execute')
            ->with('update')
            ->willReturn(false);
        $this->analyticsTokenMock
            ->method('isTokenExist')
            ->willReturn(false);
        $this->addFinalOutputAsserts();
        $this->assertFalse($this->update->execute());
    }

    /**
     * @param bool $isExecuted
     */
    private function addFinalOutputAsserts(bool $isExecuted = true)
    {
        $this->flagManagerMock
            ->expects($this->exactly(2 * $isExecuted))
            ->method('deleteFlag')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE] => $this->flagManagerMock,
                [SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE] => $this->flagManagerMock
            });
        $this->configWriterMock
            ->expects($this->exactly((int)$isExecuted))
            ->method('delete')
            ->with(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH);
        $this->reinitableConfigMock
            ->expects($this->exactly((int)$isExecuted))
            ->method('reinit')
            ->with();
    }

    /**
     * @param $counterData
     * @return void
     * @dataProvider executeWithEmptyReverseCounterDataProvider
     * @throws NotFoundException
     */
    public function testExecuteWithEmptyReverseCounter($counterData)
    {
        $this->flagManagerMock
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($counterData);
        $this->connectorMock
            ->expects($this->never())
            ->method('execute')
            ->with('update')
            ->willReturn(false);
        $this->analyticsTokenMock
            ->method('isTokenExist')
            ->willReturn(true);
        $this->addFinalOutputAsserts();
        $this->assertFalse($this->update->execute());
    }

    /**
     * Provides empty states of the reverse counter.
     *
     * @return array
     */
    public static function executeWithEmptyReverseCounterDataProvider()
    {
        return [
            [null],
            [0]
        ];
    }

    /**
     * @param int $reverseCount
     * @param bool $commandResult
     * @param bool $finalConditionsIsExpected
     * @param bool $functionResult
     * @return void
     * @dataProvider executeRegularScenarioDataProvider
     * @throws NotFoundException
     */
    public function testExecuteRegularScenario(
        int $reverseCount,
        bool $commandResult,
        bool $finalConditionsIsExpected,
        bool $functionResult
    ) {
        $this->flagManagerMock
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($reverseCount);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $reverseCount - 1);
        $this->connectorMock
            ->expects($this->once())
            ->method('execute')
            ->with('update')
            ->willReturn($commandResult);
        $this->analyticsTokenMock
            ->method('isTokenExist')
            ->willReturn(true);
        $this->addFinalOutputAsserts($finalConditionsIsExpected);
        $this->assertSame($functionResult, $this->update->execute());
    }

    /**
     * @return array
     */
    public static function executeRegularScenarioDataProvider()
    {
        return [
            'The last attempt with command execution result False' => [
                'Reverse count' => 1,
                'Command result' => false,
                'Executed final output conditions' => true,
                'Function result' => false,
            ],
            'Not the last attempt with command execution result False' => [
                'Reverse count' => 10,
                'Command result' => false,
                'Executed final output conditions' => false,
                'Function result' => false,
            ],
            'Command execution result True' => [
                'Reverse count' => 10,
                'Command result' => true,
                'Executed final output conditions' => true,
                'Function result' => true,
            ],
        ];
    }
}
