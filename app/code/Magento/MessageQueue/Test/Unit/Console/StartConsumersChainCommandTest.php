<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Console;

use Magento\Framework\Console\Cli;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MessageQueue\Console\StartConsumersChainCommand;
use Magento\MessageQueue\Model\ConsumersRunnerExecutor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Unit tests for StartConsumersChainCommand.
 */
class StartConsumersChainCommandTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConsumersRunnerExecutor|MockObject
     */
    private $consumersRunnerExecutorMock;

    /**
     * @var StartConsumersChainCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->consumersRunnerExecutorMock = $this->createMock(ConsumersRunnerExecutor::class);
        $this->objectManager = new ObjectManager($this);
        $this->command = $this->objectManager->getObject(
            StartConsumersChainCommand::class,
            [
                'consumersRunnerExecutor' => $this->consumersRunnerExecutorMock,
            ]
        );
    }

    public function testExecuteStartsAllConsumersByDefault(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->expects($this->once())
            ->method('getArgument')
            ->with(StartConsumersChainCommand::ARGUMENT_CONSUMERS)
            ->willReturn([]);

        $this->consumersRunnerExecutorMock->expects($this->once())
            ->method('run')
            ->with([], 0, [], []);

        $this->assertSame(Cli::RETURN_SUCCESS, $this->command->run($input, $output));
    }

    public function testExecuteStartsSelectedConsumers(): void
    {
        $consumers = ['queue.name.1', 'queue.name.2'];
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->expects($this->once())
            ->method('getArgument')
            ->with(StartConsumersChainCommand::ARGUMENT_CONSUMERS)
            ->willReturn($consumers);

        $this->consumersRunnerExecutorMock->expects($this->once())
            ->method('run')
            ->with([], 0, [], $consumers);

        $this->assertSame(Cli::RETURN_SUCCESS, $this->command->run($input, $output));
    }

    public function testConfigure(): void
    {
        $this->assertSame(
            StartConsumersChainCommand::COMMAND_QUEUE_CONSUMERS_START_CHAIN,
            $this->command->getName()
        );
        $this->assertSame(
            'Start all MessageQueue consumers or a selected chain of consumers',
            $this->command->getDescription()
        );
        $this->command->getDefinition()->getArgument(StartConsumersChainCommand::ARGUMENT_CONSUMERS);
        $this->assertStringContainsString('%command.full_name% consumer.one consumer.two', $this->command->getHelp());
    }
}
