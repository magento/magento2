<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\ShellInterface;
use Magento\MessageQueue\Model\CheckIsAvailableMessagesInQueue;
use Magento\MessageQueue\Model\ConsumersRunnerExecutor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Unit tests for ConsumersRunnerExecutor.
 */
class ConsumersRunnerExecutorTest extends TestCase
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
     * @var ConnectionTypeResolver|MockObject
     */
    private $connectionTypeResolver;

    /**
     * @var ConsumersRunnerExecutor
     */
    private $consumersRunnerExecutor;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->phpExecutableFinderMock = $this->createMock(PhpExecutableFinder::class);
        $this->lockManagerMock = $this->createMock(LockManagerInterface::class);
        $this->shellBackgroundMock = $this->createMock(ShellInterface::class);
        $this->consumerConfigMock = $this->createMock(ConsumerConfigInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->checkIsAvailableMessagesMock = $this->createMock(CheckIsAvailableMessagesInQueue::class);
        $this->connectionTypeResolver = $this->createMock(ConnectionTypeResolver::class);
        $this->connectionTypeResolver->method('getConnectionType')->willReturn('something');
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->consumersRunnerExecutor = new ConsumersRunnerExecutor(
            $this->phpExecutableFinderMock,
            $this->consumerConfigMock,
            $this->deploymentConfigMock,
            $this->shellBackgroundMock,
            $this->lockManagerMock,
            $this->connectionTypeResolver,
            $this->loggerMock,
            $this->checkIsAvailableMessagesMock
        );
    }

    /**
     * @param int $maxMessages
     * @param int $maxMessagesConsumer
     * @param bool $isLocked
     * @param string $php
     * @param string $command
     * @param array $arguments
     * @param array $allowedConsumers
     * @param int $shellBackgroundExpects
     * @param int $isRunExpects
     */
    #[DataProvider('runDataProvider')]
    public function testRun(
        int $maxMessages,
        int $maxMessagesConsumer,
        bool $isLocked,
        string $php,
        string $command,
        array $arguments,
        array $allowedConsumers,
        int $shellBackgroundExpects,
        int $isRunExpects
    ): void {
        $consumerName = 'consumerName';

        $this->deploymentConfigMock->method('get')
            ->willReturnMap([
                ['queue/only_spawn_when_message_available', true, false],
            ]);

        $consumer = $this->createMock(ConsumerConfigItemInterface::class);
        $consumer->method('getName')->willReturn($consumerName);
        $consumer->method('getMaxMessages')->willReturn($maxMessagesConsumer);

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

        $this->consumersRunnerExecutor->run([], $maxMessages, $allowedConsumers);
    }

    public static function runDataProvider(): array
    {
        return [
            [
                'maxMessages' => 20000,
                'maxMessagesConsumer' => 20000,
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
                'maxMessagesConsumer' => 30000,
                'isLocked' => false,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=30000'],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 10000,
                'maxMessagesConsumer' => 10000,
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
                'maxMessagesConsumer' => 10000,
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
                'maxMessagesConsumer' => 10000,
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
                'maxMessagesConsumer' => 10000,
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
                'maxMessagesConsumer' => 500,
                'isLocked' => false,
                'php' => '',
                'command' => 'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                'arguments' => ['consumerName', '--single-thread', '--max-messages=500'],
                'allowedConsumers' => ['consumerName'],
                'shellBackgroundExpects' => 1,
                'isRunExpects' => 1,
            ],
            [
                'maxMessages' => 0,
                'maxMessagesConsumer' => 0,
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

    public function testRunLogsWarningWhenConsumerMaxMessagesExceedsConfiguredDefault(): void
    {
        $consumerName = 'consumerName';
        $maxMessages = 10000;
        $consumerMaxMessages = 30000;

        $this->deploymentConfigMock->method('get')
            ->willReturnMap([
                ['queue/only_spawn_when_message_available', true, false],
            ]);

        $consumer = $this->createMock(ConsumerConfigItemInterface::class);
        $consumer->method('getName')->willReturn($consumerName);
        $consumer->method('getMaxMessages')->willReturn($consumerMaxMessages);

        $this->phpExecutableFinderMock->method('find')->willReturn('');
        $this->consumerConfigMock->method('getConsumers')->willReturn([$consumer]);
        $this->lockManagerMock->method('isLocked')->willReturn(false);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with($this->callback(function ($message) use ($consumerName, $maxMessages, $consumerMaxMessages) {
                return $message instanceof Phrase
                    && str_contains((string)$message, $consumerName)
                    && str_contains((string)$message, (string)$consumerMaxMessages)
                    && str_contains((string)$message, (string)$maxMessages);
            }));

        $this->shellBackgroundMock->expects($this->once())
            ->method('execute');

        $this->consumersRunnerExecutor->run([], $maxMessages, []);
    }

    public function testRunFiltersByExplicitConsumerNames(): void
    {
        $firstConsumer = $this->createMock(ConsumerConfigItemInterface::class);
        $firstConsumer->method('getName')->willReturn('consumer.one');
        $secondConsumer = $this->createMock(ConsumerConfigItemInterface::class);
        $secondConsumer->method('getName')->willReturn('consumer.two');

        $this->deploymentConfigMock->method('get')
            ->willReturnMap([
                ['queue/only_spawn_when_message_available', true, false],
            ]);
        $this->phpExecutableFinderMock->method('find')->willReturn('');
        $this->consumerConfigMock->expects($this->once())
            ->method('getConsumers')
            ->willReturn([$firstConsumer, $secondConsumer]);
        $this->lockManagerMock->expects($this->once())
            ->method('isLocked')
            ->with(md5('consumer.two')) //phpcs:ignore
            ->willReturn(false);
        $this->shellBackgroundMock->expects($this->once())
            ->method('execute')
            ->with(
                'php ' . BP . '/bin/magento queue:consumers:start %s %s',
                ['consumer.two', '--single-thread']
            );

        $this->consumersRunnerExecutor->run([], 0, [], ['consumer.two']);
    }

    /**
     * @param int $maxMessages
     * @param array $isLocked
     * @param string $php
     * @param array $returnMap
     * @param array $allowedConsumers
     * @param int $shellBackgroundExpects
     */
    #[DataProvider('runMultiProcessesDataProvider')]
    public function testRunMultiProcesses(
        int $maxMessages,
        array $isLocked,
        string $php,
        array $returnMap,
        array $allowedConsumers,
        int $shellBackgroundExpects
    ): void {
        $consumerName = 'consumerName';

        $this->deploymentConfigMock->method('get')
            ->willReturnMap([
                ['queue/only_spawn_when_message_available', true, false],
            ]);

        $consumer = $this->createMock(ConsumerConfigItemInterface::class);
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
                [md5($consumerName . '-' . 1)] => $isLocked[0], //phpcs:ignore
                [md5($consumerName . '-' . 2)] => $isLocked[1], //phpcs:ignore
            });

        $this->shellBackgroundMock->expects(self::exactly($shellBackgroundExpects))
            ->method('execute')
            ->willReturnMap($returnMap);

        $this->consumersRunnerExecutor->run(['consumerName' => 2], $maxMessages, $allowedConsumers);
    }

    public static function runMultiProcessesDataProvider(): array
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
                        'value1',
                    ],
                    [
                        'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                        ['consumerName', '--multi-process=2', '--max-messages=20000'],
                        'value2',
                    ],
                ],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 2,
            ],
            [
                'maxMessages' => 20000,
                'isLocked' => [true, false],
                'php' => '',
                'returnMap' => [
                    [
                        'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                        ['consumerName', '--multi-process=2', '--max-messages=20000'],
                        'value2',
                    ],
                ],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 1,
            ],
            [
                'maxMessages' => 20000,
                'isLocked' => [true, true],
                'php' => '',
                'returnMap' => [
                    [
                        'php ' . BP . '/bin/magento queue:consumers:start %s %s %s',
                        ['consumerName', '--multi-process=2', '--max-messages=20000'],
                        'value2',
                    ],
                ],
                'allowedConsumers' => [],
                'shellBackgroundExpects' => 0,
            ],
        ];
    }

    public function testRunSkipsConsumerWhenConnectionIsNotConfigured(): void
    {
        $consumerName = 'consumerName';
        $connectionName = 'connectionName';

        $consumer = $this->createMock(ConsumerConfigItemInterface::class);
        $consumer->method('getName')->willReturn($consumerName);
        $consumer->method('getConnection')->willReturn($connectionName);

        $this->phpExecutableFinderMock->method('find')->willReturn('');
        $this->consumerConfigMock->expects($this->once())
            ->method('getConsumers')
            ->willReturn([$consumer]);
        $this->connectionTypeResolver->expects($this->once())
            ->method('getConnectionType')
            ->with($connectionName)
            ->willThrowException(new \LogicException('Connection is unavailable'));
        $this->deploymentConfigMock->expects($this->never())
            ->method('get');
        $this->lockManagerMock->expects($this->never())
            ->method('isLocked');
        $this->checkIsAvailableMessagesMock->expects($this->never())
            ->method('execute');
        $this->shellBackgroundMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($this->callback(function ($message) use ($consumerName, $connectionName) {
                return str_contains((string)$message, $consumerName)
                    && str_contains((string)$message, $connectionName)
                    && str_contains((string)$message, 'Connection is unavailable');
            }));

        $this->consumersRunnerExecutor->run([], 1000, []);
    }

    public function testRunSkipsConsumerWhenQueueIsNotAvailable(): void
    {
        $consumerName = 'consumerName';
        $connectionName = 'connectionName';
        $queueName = 'queueName';

        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('queue/only_spawn_when_message_available', true)
            ->willReturn(true);

        $consumer = $this->createMock(ConsumerConfigItemInterface::class);
        $consumer->method('getName')->willReturn($consumerName);
        $consumer->method('getConnection')->willReturn($connectionName);
        $consumer->method('getQueue')->willReturn($queueName);
        $consumer->method('getOnlySpawnWhenMessageAvailable')->willReturn(true);

        $this->phpExecutableFinderMock->method('find')->willReturn('');
        $this->consumerConfigMock->expects($this->once())
            ->method('getConsumers')
            ->willReturn([$consumer]);
        $this->checkIsAvailableMessagesMock->expects($this->once())
            ->method('execute')
            ->with($connectionName, $queueName)
            ->willThrowException(new \LogicException('Queue is unavailable'));
        $this->lockManagerMock->expects($this->never())
            ->method('isLocked');
        $this->shellBackgroundMock->expects($this->never())
            ->method('execute');

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($this->callback(function ($message) use ($consumerName, $queueName) {
                return str_contains((string)$message, $consumerName)
                    && str_contains((string)$message, $queueName)
                    && str_contains((string)$message, 'Queue is unavailable');
            }));

        $this->consumersRunnerExecutor->run([], 1000, []);
    }

    /**
     * @param bool|null $onlySpawnWhenMessageAvailable
     * @param bool $isMessagesAvailableInTheQueue
     * @param int $shellBackgroundExpects
     * @param bool $globalOnlySpawnWhenMessageAvailable
     * @param int $getOnlySpawnWhenMessageAvailableCallCount
     * @param int $isMessagesAvailableInTheQueueCallCount
     */
    #[DataProvider('runBasedOnOnlySpawnWhenMessageAvailableConsumerConfigurationDataProvider')]
    public function testRunBasedOnOnlySpawnWhenMessageAvailableConsumerConfiguration(
        ?bool $onlySpawnWhenMessageAvailable,
        bool $isMessagesAvailableInTheQueue,
        int $shellBackgroundExpects,
        bool $globalOnlySpawnWhenMessageAvailable,
        int $getOnlySpawnWhenMessageAvailableCallCount,
        int $isMessagesAvailableInTheQueueCallCount
    ): void {
        $consumerName = 'consumerName';
        $connectionName = 'connectionName';
        $queueName = 'queueName';

        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('queue/only_spawn_when_message_available', true)
            ->willReturn($globalOnlySpawnWhenMessageAvailable);

        $consumer = $this->createMock(ConsumerConfigItemInterface::class);
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

        $this->checkIsAvailableMessagesMock->expects($this->exactly($isMessagesAvailableInTheQueueCallCount))
            ->method('execute')
            ->willReturn($isMessagesAvailableInTheQueue);

        $this->shellBackgroundMock->expects($this->exactly($shellBackgroundExpects))
            ->method('execute');

        $this->consumersRunnerExecutor->run([], 1000, []);
    }

    public static function runBasedOnOnlySpawnWhenMessageAvailableConsumerConfigurationDataProvider(): array
    {
        return [
            [
                'onlySpawnWhenMessageAvailable' => true,
                'isMessagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 1,
                'isMessagesAvailableInTheQueueCallCount' => 1,
            ],
            [
                'onlySpawnWhenMessageAvailable' => true,
                'isMessagesAvailableInTheQueue' => false,
                'shellBackgroundExpects' => 0,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 1,
                'isMessagesAvailableInTheQueueCallCount' => 1,
            ],
            [
                'onlySpawnWhenMessageAvailable' => false,
                'isMessagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMessagesAvailableInTheQueueCallCount' => 0,
            ],
            [
                'onlySpawnWhenMessageAvailable' => null,
                'isMessagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => true,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMessagesAvailableInTheQueueCallCount' => 1,
            ],
            [
                'onlySpawnWhenMessageAvailable' => null,
                'isMessagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => false,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMessagesAvailableInTheQueueCallCount' => 0,
            ],
            [
                'onlySpawnWhenMessageAvailable' => false,
                'isMessagesAvailableInTheQueue' => true,
                'shellBackgroundExpects' => 1,
                'globalOnlySpawnWhenMessageAvailable' => true,
                'getOnlySpawnWhenMessageAvailableCallCount' => 2,
                'isMessagesAvailableInTheQueueCallCount' => 0,
            ],
        ];
    }
}
