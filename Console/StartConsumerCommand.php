<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;

/**
 * Command for starting MessageQueue consumers.
 */
class StartConsumerCommand extends Command
{
    const ARGUMENT_CONSUMER = 'consumer';
    const OPTION_NUMBER_OF_MESSAGES = 'max-messages';
    const COMMAND_QUEUE_CONSUMERS_START = 'queue:consumers:start';

    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * StartConsumerCommand constructor.
     * {@inheritdoc}
     *
     * @param \Magento\Framework\App\State $appState
     * @param ConsumerFactory $consumerFactory
     */
    public function __construct(
        $name = null,
        \Magento\Framework\App\State $appState,
        ConsumerFactory $consumerFactory
    ) {
        $appState->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->consumerFactory = $consumerFactory;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerName = $input->getArgument(self::ARGUMENT_CONSUMER);
        $numberOfMessages = $input->getOption(self::OPTION_NUMBER_OF_MESSAGES);
        $consumer = $this->consumerFactory->get($consumerName);
        $consumer->process($numberOfMessages);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_CONSUMERS_START);
        $this->setDescription('Start MessageQueue consumer');
        $this->addArgument(
            self::ARGUMENT_CONSUMER,
            InputArgument::REQUIRED,
            'The name of the consumer to be started.'
        );
        $this->addOption(
            self::OPTION_NUMBER_OF_MESSAGES,
            null,
            InputOption::VALUE_REQUIRED,
            'The number of messages to be processed by the consumer before process termination. '
            . 'If not specified - terminate after processing all queued messages.'
        );
        $this->setHelp(
            <<<HELP
This command starts MessageQueue consumer by its name.

To start consumer which will process all queued messages and terminate execution:

      <comment>%command.full_name% someConsumer</comment>

To specify the number of messages which should be processed by consumer before its termination:

    <comment>%command.full_name% someConsumer --max-messages=50</comment>
HELP
        );
        parent::configure();
    }
}
