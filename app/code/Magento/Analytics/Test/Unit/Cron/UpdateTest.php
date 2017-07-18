<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Cron\Update;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

/**
 * Class Update
 */
class UpdateTest extends \PHPUnit_Framework_TestCase
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
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var Update
     */
    private $update;

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
        $this->analyticsTokenMock = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

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
     */
    public function testExecuteWithoutToken()
    {
        $this->flagManagerMock
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(10);
        $this->connectorMock
            ->expects($this->once())
            ->method('execute')
            ->with('update')
            ->willReturn(false);
        $this->analyticsTokenMock
            ->expects($this->once())
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
            ->withConsecutive(
                [SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE],
                [SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE]
            );
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
    public function executeWithEmptyReverseCounterDataProvider()
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
    public function executeRegularScenarioDataProvider()
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
