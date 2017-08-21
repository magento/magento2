<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Test\Unit\Model\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\ShellInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;

class ConsumersRunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var ShellInterface|MockObject
     */
    private $shellMock;

    /**
     * @var ShellInterface|MockObject
     */
    private $shellBackgroundMock;

    /**
     * @var ConsumerConfigInterface|MockObject
     */
    private $consumerConfigMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConsumersRunner
     */
    private $consumersRunner;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->shellMock = $this->getMockBuilder(ShellInterface::class)
            ->getMockForAbstractClass();
        $this->shellBackgroundMock = $this->getMockBuilder(ShellInterface::class)
            ->getMockForAbstractClass();
        $this->consumerConfigMock = $this->getMockBuilder(ConsumerConfigInterface::class)
            ->getMockForAbstractClass();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->consumersRunner = new ConsumersRunner(
            $this->consumerConfigMock,
            $this->deploymentConfigMock,
            $this->shellMock,
            $this->shellBackgroundMock
        );
    }

    /**
     * @param $cronRun
     * @param $maxMessages
     * @param $psAux
     * @param $getConsumersExpects
     * @param $shellBackgroundExpects
     * @dataProvider runDataProvider
     */
    public function testRun(
        $cronRun,
        $maxMessages,
        $psAux,
        $getConsumersExpects,
        $shellBackgroundExpects
    ) {
        $this->deploymentConfigMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['queue_consumer/cron_run', true, $cronRun],
                ['queue_consumer/max_messages', 10000, $maxMessages],
            ]);

        $this->shellMock->expects($this->once())
            ->method('execute')
            ->with("ps aux | grep '[q]ueue:consumers:start' | awk '{print $2}'")
            ->willReturn($psAux);

        /** @var ConsumerConfigInterface|MockObject $firstCunsumer */
        $firstConsumer = $this->getMockBuilder(ConsumerConfigItemInterface::class)
            ->getMockForAbstractClass();
        $firstConsumer->expects($this->any())
            ->method('getName')
            ->willReturn('firstConsumer');

        /** @var ConsumerConfigInterface|MockObject $firstCunsumer */
        $secondConsumer = $this->getMockBuilder(ConsumerConfigItemInterface::class)
            ->getMockForAbstractClass();
        $secondConsumer->expects($this->any())
            ->method('getName')
            ->willReturn('secondConsumer');

        $this->consumerConfigMock->expects($getConsumersExpects)
            ->method('getConsumers')
            ->willReturn([$firstConsumer, $secondConsumer]);

        $this->shellBackgroundMock->expects($shellBackgroundExpects)
            ->method('execute')
            ->withConsecutive(
                ['php '. BP . '/bin/magento queue:consumers:start firstConsumer --max-messages=' . $maxMessages],
                ['php '. BP . '/bin/magento queue:consumers:start secondConsumer --max-messages=' . $maxMessages]
            );

        $this->consumersRunner->run();
    }

    /**
     * @return array
     */
    public function runDataProvider()
    {
        return [
            [
                'cronRun' => true,
                'maxMessages' => 20000,
                'psAux' => '',
                'getConsumersExpects' => $this->once(),
                'shellBackgroundExpects' => $this->exactly(2),
            ],
            [
                'cronRun' => false,
                'maxMessages' => 10000,
                'psAux' => '',
                'getConsumersExpects' => $this->never(),
                'shellBackgroundExpects' => $this->never(),
            ],
            [
                'cronRun' => true,
                'maxMessages' => 20000,
                'psAux' => 'some process runs',
                'getConsumersExpects' => $this->never(),
                'shellBackgroundExpects' => $this->never(),
            ],
            [
                'cronRun' => false,
                'maxMessages' => 10000,
                'psAux' => 'some process runs',
                'getConsumersExpects' => $this->never(),
                'shellBackgroundExpects' => $this->never(),
            ],
        ];
    }
}
