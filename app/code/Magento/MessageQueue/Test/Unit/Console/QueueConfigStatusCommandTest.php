<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Console;

use Magento\Framework\Console\Cli;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigChangeDetectorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MessageQueue\Console\QueueConfigStatusCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class QueueConfigStatusCommandTest extends TestCase
{
    /**
     * @var QueueConfigChangeDetectorInterface|MockObject
     */
    private $changeDetector;

    /**
     * @var QueueConfigStatusCommand
     */
    private $command;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->changeDetector = $this->createMock(QueueConfigChangeDetectorInterface::class);
        $objectManager = new ObjectManager($this);
        $this->command = $objectManager->getObject(
            QueueConfigStatusCommand::class,
            ['changeDetector' => $this->changeDetector]
        );
    }

    /**
     * @return void
     */
    public function testExecuteWhenUpToDate(): void
    {
        $this->changeDetector->method('getMissingQueues')->willReturn([]);
        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertStringContainsString('up to date', $tester->getDisplay());
    }

    /**
     * @return void
     */
    public function testExecuteWhenUpgradeRequired(): void
    {
        $this->changeDetector->method('getMissingQueues')->willReturn(['repro.issue.38225']);
        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertSame(QueueConfigStatusCommand::EXIT_CODE_UPGRADE_REQUIRED, $exitCode);
        $this->assertStringContainsString('setup:upgrade', $tester->getDisplay());
        $this->assertStringContainsString('repro.issue.38225', $tester->getDisplay());
    }
}
