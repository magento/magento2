<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Model\Cron;

use Magento\Framework\App\DeploymentConfig;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;
use Magento\MessageQueue\Model\ConsumersRunnerExecutor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ConsumersRunner.
 */
class ConsumersRunnerTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConsumersRunnerExecutor|MockObject
     */
    private $consumersRunnerExecutorMock;

    /**
     * @var ConsumersRunner
     */
    private $consumersRunner;

    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->consumersRunnerExecutorMock = $this->createMock(ConsumersRunnerExecutor::class);

        $this->consumersRunner = new ConsumersRunner(
            $this->deploymentConfigMock,
            $this->consumersRunnerExecutorMock
        );
    }

    public function testRunDisabled(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('cron_consumers_runner/cron_run', true)
            ->willReturn(false);

        $this->consumersRunnerExecutorMock->expects($this->never())
            ->method('run');

        $this->consumersRunner->run();
    }

    public function testRunDelegatesResolvedDeploymentConfig(): void
    {
        $multipleProcesses = ['consumerName' => 2];
        $maxMessages = 500;
        $allowedConsumers = ['consumerName'];

        $this->deploymentConfigMock->expects($this->exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    ['cron_consumers_runner/cron_run', true, true],
                    ['cron_consumers_runner/multiple_processes', [], $multipleProcesses],
                    ['cron_consumers_runner/max_messages', 10000, $maxMessages],
                    ['cron_consumers_runner/consumers', [], $allowedConsumers],
                ]
            );

        $this->consumersRunnerExecutorMock->expects($this->once())
            ->method('run')
            ->with($multipleProcesses, $maxMessages, $allowedConsumers);

        $this->consumersRunner->run();
    }
}
