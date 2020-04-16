<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Test\Unit\Model\Cron;

use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use \PHPUnit\Framework\MockObject\MockObject as MockObject;
use Magento\Framework\ShellInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;
use Symfony\Component\Process\PhpExecutableFinder;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * Unit tests for ConsumersRunner.
 */
class ConsumersRunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManagerMock;

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
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResover;

    /**
     * @var ConsumersRunner
     */
    private $consumersRunner;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        require_once __DIR__ . '/../../_files/consumers_runner_functions_mocks.php';

        $this->phpExecutableFinderMock = $this->getMockBuilder(phpExecutableFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lockManagerMock = $this->getMockBuilder(LockManagerInterface::class)
            ->getMockForAbstractClass();
        $this->shellBackgroundMock = $this->getMockBuilder(ShellInterface::class)
            ->getMockForAbstractClass();
        $this->consumerConfigMock = $this->getMockBuilder(ConsumerConfigInterface::class)
            ->getMockForAbstractClass();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionTypeResover = $this->getMockBuilder(ConnectionTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionTypeResover->method('getConnectionType')->willReturn('something');

        $this->consumersRunner = new ConsumersRunner(
            $this->phpExecutableFinderMock,
            $this->consumerConfigMock,
            $this->deploymentConfigMock,
            $this->shellBackgroundMock,
            $this->lockManagerMock,
            $this->connectionTypeResover
        );
    }

    public function testRunDisabled()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturnMap(
                [
                    ['cron_consumers_runner/cron_run', true, false],
                    ['cron_consumers_runner/max_messages', 10000, 10000],
                    ['cron_consumers_runner/consumers', [], []],
                ]
            );

        $this->consumerConfigMock->expects($this->never())
            ->method('getConsumers');
        $this->lockManagerMock->expects($this->never())
            ->method('isLocked');
        $this->shellBackgroundMock->expects($this->never())
            ->method('execute');

        $this->consumersRunner->run();
    }

    /**
     * @param int $maxMessages
     * @param bool $isLocked
     * @param string $php
     * @param string $command
     * @param array $arguments
     * @param array $allowedConsumers
     * @param int $shellBackgroundExpects
     * @param int $isRunExpects
     * @dataProvider runDataProvider
     */
    public function testRun(
        $maxMessages,
        $isLocked,
        $php,
        $command,
        $arguments,
        array $allowedConsumers,
        $shellBackgroundExpects,
        $isRunExpects
    ) {
        $consumerName = 'consumerName';

        $this->deploymentConfigMock->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap(
                [
                    ['cron_consumers_runner/cron_run', true, true],
                    ['cron_consumers_runner/max_messages', 10000, $maxMessages],
                    ['cron_consumers_runner/consumers', [], $allowedConsumers],
                ]
            );

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

        $this->lockManagerMock->expects($this->exactly($isRunExpects))
            ->method('isLocked')
            ->with(md5($consumerName)) //phpcs:ignore
            ->willReturn($isLocked);

        $this->shellBackgroundMock->expects($this->exactly($shellBackgroundExpects))
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
                'isLocked' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=20000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['someConsumer'],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 0,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => true,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['someConsumer'],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 0,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => true,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => true,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php '. BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 0,
                'isLocked' => false,
                'php' => '/bin/php',
                'command' => '/bin/php '. BP . '/bin/magento queue:consumers:start %s %s',
                'arguments' => ['consumerName', '--single-thread'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
        ];
    }
}
