<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;
use Magento\Framework\MessageQueue\Topology\SynchronizerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\QueueUpgradeCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class QueueUpgradeCommandTest extends TestCase
{
    /**
     * @var ObjectManagerProvider|MockObject
     */
    private $objectManagerProvider;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var SynchronizerInterface|MockObject
     */
    private $synchronizer;

    /**
     * @var QueueUpgradeCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->synchronizer = $this->createMock(SynchronizerInterface::class);

        $this->objectManagerProvider->method('get')->willReturn($this->objectManager);
        $this->objectManager->method('get')
            ->with(SynchronizerInterface::class)
            ->willReturn($this->synchronizer);

        $this->command = new QueueUpgradeCommand($this->objectManagerProvider, $this->deploymentConfig);
    }

    public function testExecuteAppliesAndListsTopology(): void
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->synchronizer->expects($this->once())
            ->method('synchronize')
            ->willReturn([
                'Exchange "magento-log" is in place on connection "amqp".',
                'Queue "product_action_update" is in place on connection "amqp".',
            ]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $display = $tester->getDisplay();
        $this->assertStringContainsString('Exchange "magento-log" is in place', $display);
        $this->assertStringContainsString('Queue "product_action_update" is in place', $display);
        $this->assertSame(Cli::RETURN_SUCCESS, $tester->getStatusCode());
    }

    public function testExecuteWithNothingToApply(): void
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->synchronizer->expects($this->once())->method('synchronize')->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertStringContainsString('No message queue topology needed to be applied.', $tester->getDisplay());
        $this->assertSame(Cli::RETURN_SUCCESS, $tester->getStatusCode());
    }

    public function testExecuteNotInstalled(): void
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->synchronizer->expects($this->never())->method('synchronize');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertStringMatchesFormat(
            '%ANo information is available: the Magento application is not installed.%w',
            $tester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }

    public function testExecuteSynchronizationFailure(): void
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->synchronizer->expects($this->once())
            ->method('synchronize')
            ->willThrowException(new \Exception('Broker unreachable'));

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertStringContainsString('Broker unreachable', $tester->getDisplay());
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }
}
