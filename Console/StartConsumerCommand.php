<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    const OPTION_AREACODE = 'area-code';
    const COMMAND_QUEUE_CONSUMERS_START = 'queue:consumers:start';

    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * StartConsumerCommand constructor.
     * {@inheritdoc}
     *
     * @param \Magento\Framework\App\State $appState
     * @param ConsumerFactory $consumerFactory
     * @param string $name
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        ConsumerFactory $consumerFactory,
        $name = null
    ) {
        $this->appState = $appState;
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
        $areaCode = $input->getOption(self::OPTION_AREACODE);
        if ($areaCode !== null) {
            $this->appState->setAreaCode($areaCode);
        } else {
            $this->appState->setAreaCode('global');
        }
        $consumer = $this->consumerFactory->get($consumerName);
        $consumer->process($numberOfMessages);
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
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
        $this->addOption(
            self::OPTION_AREACODE,
            null,
            InputOption::VALUE_REQUIRED,
            'The preferred area (global, adminhtml, etc...) '
            . 'default is global.'
        );
        $this->setHelp(
            <<<HELP
This command starts MessageQueue consumer by its name.

To start consumer which will process all queued messages and terminate execution:

      <comment>%command.full_name% someConsumer</comment>

To specify the number of messages which should be processed by consumer before its termination:

    <comment>%command.full_name% someConsumer --max-messages=50</comment>

To specify the preferred area:

    <comment>%command.full_name% someConsumer --area-code='adminhtml'</comment>
HELP
        );
        parent::configure();
    }
}
