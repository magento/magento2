<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\MessageQueue\Console;

use Magento\Framework\Console\Cli;
use Magento\MessageQueue\Model\ConsumersRunnerExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for starting a selected chain of MessageQueue consumers.
 */
class StartConsumersChainCommand extends Command
{
    public const ARGUMENT_CONSUMERS = 'consumers';
    public const COMMAND_QUEUE_CONSUMERS_START_CHAIN = 'queue:consumers:start-chain';

    /**
     * @var ConsumersRunnerExecutor
     */
    private $consumersRunnerExecutor;

    /**
     * @param ConsumersRunnerExecutor $consumersRunnerExecutor
     * @param string|null $name
     */
    public function __construct(
        ConsumersRunnerExecutor $consumersRunnerExecutor,
        ?string $name = null
    ) {
        $this->consumersRunnerExecutor = $consumersRunnerExecutor;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumers = $input->getArgument(self::ARGUMENT_CONSUMERS);

        $this->consumersRunnerExecutor->run([], 0, [], $consumers);

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_CONSUMERS_START_CHAIN);
        $this->setDescription('Start all MessageQueue consumers or a selected chain of consumers');
        $this->addArgument(
            self::ARGUMENT_CONSUMERS,
            InputArgument::IS_ARRAY,
            'Optional list of consumer names to start. If omitted, all consumers are started.'
        );
        $this->setHelp(
            <<<HELP
This command starts all MessageQueue consumers by default.

To start all configured consumers:

    <comment>%command.full_name%</comment>

To start only selected consumers:

    <comment>%command.full_name% consumer.one consumer.two</comment>
HELP
        );
        parent::configure();
    }
}
