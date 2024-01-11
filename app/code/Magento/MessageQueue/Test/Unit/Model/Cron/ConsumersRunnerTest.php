<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Model\Cron;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\ShellInterface;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;
use Magento\MessageQueue\Model\CheckIsAvailableMessagesInQueue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Unit tests for ConsumersRunner.
 */
class ConsumersRunnerTest extends TestCase
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
     * @var CheckIsAvailableMessagesInQueue|MockObject
     */
    private $checkIsAvailableMessagesMock;

    /**
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResolver;

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

        $this->phpExecutableFinderMock = $this->getMockBuilder(PhpExecutableFinder::class)
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
        $this->checkIsAvailableMessagesMock = $this->createMock(CheckIsAvailableMessagesInQueue::class);
        $this->connectionTypeResolver = $this->getMockBuilder(ConnectionTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionTypeResolver->method('getConnectionType')->willReturn('something');

        $this->consumersRunner = new ConsumersRunner(
            $this->phpExecutableFinderMock,
            $this->consumerConfigMock,
            $this->deploymentConfigMock,
            $this->shellBackgroundMock,
            $this->lockManagerMock,
            $this->connectionTypeResolver,
            null,
            $this->checkIsAvailableMessagesMock
        );
    }

    public function testRunDisabled()
    {
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap(
                [
                    ['cron_consumers_runner/cron_run', true, false],
                    ['cron_consumers_runner/multiple_processes', [], []],
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

        $this->deploymentConfigMock
            ->method('get')
            ->willReturnMap(
                [
                    ['cron_consumers_runner/cron_run', true, true],
                    ['cron_consumers_runner/max_messages', 10000, $maxMessages],
                    ['cron_consumers_runner/consumers', [], $allowedConsumers],
                    ['queue/only_spawn_when_message_available', null, 0],
                    ['cron_consumers_runner/multiple_processes', [], []]
                ]
            );

        /** @var ConsumerConfigInterface|MockObject $firstCunsumer */
        $consumer = $this->getMockBuilder(ConsumerConfigItemInterface::class)
            ->getMockForAbstractClass();
        $consumer->method('getName')->willReturn($consumerName);

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
    public static function runDataProvider()
    {
        return [
            [
                'maxMessages' => 20000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=20000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['someConsumer'],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 0,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => true,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['someConsumer'],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 0,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => true,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => true,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => 0,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=10000'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 0,
                'isLocked' => false,
                'php' => '/bin/php',
                'command' => '/bin/php ' . BP . '/bin/magento queue:consumers:start %s %s',
                'arguments' => ['consumerName', '--single-thread'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
        ];
    }

    /**
     * @param int $maxMessages
     * @param array $isLocked
     * @param string $php
     * @param array $returnMap
     * @param array $allowedConsumers
     * @param int $shellBackgroundExpects
     * @dataProvider runMultiProcessesDataProvider
     */
    public function testRunMultiProcesses(
        int    $maxMessages,
        array  $isLocked,
        string $php,
        array  $returnMap,
        array  $allowedConsumers,
        int    $shellBackgroundExpects
    ): void {
        $consumerName = 'consumerName';

        $this->deploymentConfigMock
            ->method('get')
            ->willReturnMap(
                [
                    ['cron_consumers_runner/cron_run', true, true],
                    ['cron_consumers_runner/max_messages', 10000, $maxMessages],
                    ['cron_consumers_runner/consumers', [], $allowedConsumers],
                    ['queue/only_spawn_when_message_available', null, 0],
                    ['cron_consumers_runner/multiple_processes',
                        [],
                        ['consumerName' => 2]
                    ]
                ]
            );

        /** @var ConsumerConfigInterface|MockObject $firstCunsumer */
        $consumer = $this->getMockBuilder(ConsumerConfigItemInterface::class)
            ->getMockForAbstractClass();
        $consumer->method('getName')->willReturn($consumerName);

        $this->phpExecutableFinderMock->expects($this->once())
            ->method('find')
            ->willReturn($php);

        $this->consumerConfigMock->expects($this->once())
            ->method('getConsumers')
            ->willReturn([$consumer]);

        $this->lockManagerMock->expects(self::exactly(2))
            ->method('isLocked')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [md5($consumerName . '-' . 1)] => $isLocked[0],    //phpcs:ignore
                [md5($consumerName . '-' . 2)] => $isLocked[1]     //phpcs:ignore
            });

        $this->shellBackgroundMock->expects(self::exactly($shellBackgroundExpects))
            ->method('execute')
            ->willReturnMap($returnMap);

        $this->consumersRunner->run();
    }

    /**
     * @return array
     */
    public static function runMultiProcessesDataProvider()
    {
        return [
            [
                'maxMessages' => 20000,
                'isLocked' => [false, false],
                'php' => '',
                'returnMap' => [
                    [
                        'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                        ['consumerName', '--multi-process=1', '--max-messages=20000'],
                        'value1'
                    ],
                    [
                        'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                        ['consumerName', '--multi-process=2', '--max-messages=20000'],
                        'value2'
                    ]
                ],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 2
            ],
            [
                'maxMessages' => 20000,
                'isLocked' => [true, false],
                'php' => '',
                'returnMap' => [
                    [
                        'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                        ['consumerName', '--multi-process=2', '--max-messages=20000'],
                        'value2'
                    ]
                ],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 1
            ],
            [
                'maxMessages' => 20000,
                'isLocked' => [true, true],
                'php' => '',
                'returnMap' => [
                    [
                        'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                        ['consumerName', '--multi-process=2', '--max-messages=20000'],
                        'value2'
                    ]
                ],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 0
            ],
        ];
    }

    /**
     * @param boolean $onlySpawnWhenMessageAvailable
     * @param boolean $isMassagesAvailableInTheQueue
     * @param int $shellBackgroundExpects
     * @param boolean $globalOnlySpawnWhenMessageAvailable
     * @param int $getOnlySpawnWhenMessageAvailableCallCount
     * @param int $isMassagesAvailableInTheQueueCallCount
     * @dataProvider runBasedOnOnlySpawnWhenMessageAvailableConsumerConfigurationDataProvider
     */
    public function testRunBasedOnOnlySpawnWhenMessageAvailableConsumerConfiguration(
        $onlySpawnWhenMessageAvailable,
        $isMassagesAvailableInTheQueue,
        $shellBackgroundExpects,
        $globalOnlySpawnWhenMessageAvailable,
        $getOnlySpawnWhenMessageAvailableCallCount,
        $isMassagesAvailableInTheQueueCallCount
    ) {
        $consumerName = 'consumerName';
        $connectionName = 'connectionName';
        $queueName = 'queueName';
        $this->deploymentConfigMock->expects($this->exactly(5))
            ->method('get')
            ->willReturnMap(
                [
                    ['cron_consumers_runner/cron_run', true, true],
                    ['cron_consumers_runner/max_messages', 10000, 1000],
                    ['cron_consumers_runner/consumers', [], []],
                    ['queue/only_spawn_when_message_available', true, $globalOnlySpawnWhenMessageAvailable],
                    ['cron_consumers_runner/multiple_processes', [], []]
                ]
            );

        /** @var ConsumerConfigInterface|MockObject $firstCunsumer */
        $consumer = $this->getMockBuilder(ConsumerConfigItemInterface::class)
            ->getMockForAbstractClass();
        $consumer->method('getName')->willReturn($consumerName);
        $consumer->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionName);
        $consumer->method('getQueue')->willReturn($queueName);
        $consumer->expects($this->exactly($getOnlySpawnWhenMessageAvailableCallCount))
            ->method('getOnlySpawnWhenMessageAvailable')
            ->willReturn($onlySpawnWhenMessageAvailable);
        $this->consumerConfigMock->expects($this->once())
            ->method('getConsumers')
            ->willReturn([$consumer]);

        $this->phpExecutableFinderMock->expects($this->once())
            ->method('find')
            ->willReturn('');

        $this->lockManagerMock->method('isLocked')->willReturn(false);

        $this->checkIsAvailableMessagesMock->expects($this->exactly($isMassagesAvailableInTheQueueCallCount))
            ->method('execute')
            ->willReturn($isMassagesAvailableInTheQueue);

        $this->shellBackgroundMock->expects($this->exactly($shellBackgroundExpects))
            ->method('execute');

        $this->consumersRunner->run();
    }

    /**
     * @return array
     */
    public static function runBasedOnOnlySpawnWhenMessageAvailableConsumerConfigurationDataProvider()
    {
        return [
            [
                'onlySpawnWhenMessageAvailable' => true,
                'isMassagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 1,
                'isMassagesAvailableInTheQueueCallCount' => 1
            ],
            [
                'onlySpawnWhenMessageAvailable' => true,
                'isMassagesAvailableInTheQueue' => false,
                'shellBackgroundExpects' => 0,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 1,
                'isMassagesAvailableInTheQueueCallCount' => 1
            ],
            [
                'onlySpawnWhenMessageAvailable' => false,
                'isMassagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMassagesAvailableInTheQueueCallCount' => 0
            ],
            [
                'onlySpawnWhenMessageAvailable' => null,
                'isMassagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => true,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMassagesAvailableInTheQueueCallCount' => 1
            ],
            [
                'onlySpawnWhenMessageAvailable' => null,
                'isMassagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMassagesAvailableInTheQueueCallCount' => 0
            ],
            [
                'onlySpawnWhenMessageAvailable' => false,
                'isMassagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => true,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMassagesAvailableInTheQueueCallCount' => 0
            ],
        ];
    }
}
