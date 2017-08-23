<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Test\Unit\Model\Cron;

use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\ShellInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;
use Magento\MessageQueue\Model\Cron\ConsumersRunner\Pid;
use Symfony\Component\Process\PhpExecutableFinder;

class ConsumersRunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Pid|MockObject
     */
    private $pidMock;

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
     * @var PhpExecutableFinder|MockObject
     */
    private $phpExecutableFinderMock;

    /**
     * @var ConsumersRunner
     */
    private $consumersRunner;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->phpExecutableFinderMock = $this->getMockBuilder(phpExecutableFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pidMock = $this->getMockBuilder(Pid::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shellBackgroundMock = $this->getMockBuilder(ShellInterface::class)
            ->getMockForAbstractClass();
        $this->consumerConfigMock = $this->getMockBuilder(ConsumerConfigInterface::class)
            ->getMockForAbstractClass();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->consumersRunner = new ConsumersRunner(
            $this->phpExecutableFinderMock,
            $this->consumerConfigMock,
            $this->deploymentConfigMock,
            $this->shellBackgroundMock,
            $this->pidMock
        );
    }

    public function testRunDisabled()
    {
        $this->deploymentConfigMock->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['queue_consumer/cron_run', true, false],
                ['queue_consumer/max_messages', 10000, 10000],
                ['queue_consumer/consumers', [], []],
            ]);

        $this->consumerConfigMock->expects($this->never())
            ->method('getConsumers');
        $this->pidMock->expects($this->never())
            ->method('isRun');
        $this->pidMock->expects($this->never())
            ->method('getPidFilePath');
        $this->shellBackgroundMock->expects($this->never())
            ->method('execute');

        $this->consumersRunner->run();
    }

    /**
     * @param $maxMessages
     * @param $isRun
     * @param $shellBackgroundExpects
     * @dataProvider runDataProvider
     */
    public function testRun(
        $maxMessages,
        $isRun,
        $php,
        $command,
        $arguments,
        array $allowedConsumers,
        $shellBackgroundExpects,
        $isRunExpects,
        $getPidFilePath
    ) {
        $consumerName = 'consumerName';
        $pidFilePath = '/var/consumer.pid';

        $this->deploymentConfigMock->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['queue_consumer/cron_run', true, true],
                ['queue_consumer/max_messages', 10000, $maxMessages],
                ['queue_consumer/consumers', [], $allowedConsumers],
            ]);

        /** @var ConsumerConfigInterface|MockObject $firstCunsumer */
        $consumer = $this->getMockBuilder(ConsumerConfigItemInterface::class)
            ->getMockForAbstractClass();
        $consumer->expects($this->any())
            ->method('getName')
            ->willReturn($consumerName);

        $this->phpExecutableFinderMock->expects($this->once())
            ->method('find')
            ->willReturn($php);

        $this->consumerConfigMock->expects($this->once())
            ->method('getConsumers')
            ->willReturn([$consumer]);

        $this->pidMock->expects($isRunExpects)
            ->method('isRun')
            ->with($consumerName)
            ->willReturn($isRun);
        $this->pidMock->expects($getPidFilePath)
            ->method('getPidFilePath')
            ->with($consumerName)
            ->willReturn($pidFilePath);

        $this->shellBackgroundMock->expects($shellBackgroundExpects)
            ->method('execute')
            ->with($command, $arguments);

        $this->consumersRunner->run();
    }

    /**
     * @return array
     */
    public function runDataProvider()
    {
        return [
            [
                'maxMessages' => 20000,
                'isRun' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid', '--max-messages=20000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => $this->once(),
                'isRunExpects' => $this->once(),
                'getPidFilePath' => $this->once(),
            ],
            [
                'maxMessages' => 10000,
                'isRun' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid', '--max-messages=10000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => $this->once(),
                'isRunExpects' => $this->once(),
                'getPidFilePath' => $this->once(),
            ],
            [
                'maxMessages' => 10000,
                'isRun' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid', '--max-messages=10000'],
                'allowedConsumers' => ['someConsumer'],
                'shellBackgroundExpects' => $this->never(),
                'isRunExpects' => $this->never(),
                'getPidFilePath' => $this->never(),
            ],
            [
                'maxMessages' => 10000,
                'isRun' => true,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid', '--max-messages=10000'],
                'allowedConsumers' => ['someConsumer'],
                'shellBackgroundExpects' => $this->never(),
                'isRunExpects' => $this->never(),
                'getPidFilePath' => $this->never(),
            ],
            [
                'maxMessages' => 10000,
                'isRun' => true,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid', '--max-messages=10000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => $this->never(),
                'isRunExpects' => $this->once(),
                'getPidFilePath' => $this->never(),
            ],
            [
                'maxMessages' => 10000,
                'isRun' => true,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid', '--max-messages=10000'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => $this->never(),
                'isRunExpects' => $this->once(),
                'getPidFilePath' => $this->never(),
            ],
            [
                'maxMessages' => 10000,
                'isRun' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid', '--max-messages=10000'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => $this->once(),
                'isRunExpects' => $this->once(),
                'getPidFilePath' => $this->once(),
            ],
            [
                'maxMessages' => 0,
                'isRun' => false,
                'php' => '/bin/php',
                'command' => '/bin/php '. BP . '/bin/magento queue:consumers:start %s %s',
                'arguments' => ['consumerName', '--pid-file-path=/var/consumer.pid'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => $this->once(),
                'isRunExpects' => $this->once(),
                'getPidFilePath' => $this->once(),
            ],
        ];
    }
}
