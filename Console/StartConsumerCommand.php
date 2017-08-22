<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Command for starting MessageQueue consumers.
 */
class StartConsumerCommand extends Command
{
    const ARGUMENT_CONSUMER = 'consumer';
    const OPTION_NUMBER_OF_MESSAGES = 'max-messages';
    const OPTION_BATCH_SIZE = 'batch-size';
    const OPTION_AREACODE = 'area-code';
    const PID_FILE_PATH = 'pid-file-path';
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
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * StartConsumerCommand constructor.
     * {@inheritdoc}
     *
     * @param \Magento\Framework\App\State $appState
     * @param ConsumerFactory $consumerFactory
     * @param WriteFactory $filesystem
     * @param string $name
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        ConsumerFactory $consumerFactory,
        WriteFactory $writeFactory = null,
        $name = null
    ) {
        $this->appState = $appState;
        $this->consumerFactory = $consumerFactory;
        $this->writeFactory = $writeFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(WriteFactory::class);
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerName = $input->getArgument(self::ARGUMENT_CONSUMER);
        $numberOfMessages = $input->getOption(self::OPTION_NUMBER_OF_MESSAGES);
        $batchSize = (int)$input->getOption(self::OPTION_BATCH_SIZE);
        $areaCode = $input->getOption(self::OPTION_AREACODE);
        $pidFilePath = $input->getOption(self::PID_FILE_PATH);

        if ($pidFilePath) {
            $this->savePid($pidFilePath);
        }

        if ($areaCode !== null) {
            $this->appState->setAreaCode($areaCode);
        } else {
            $this->appState->setAreaCode('global');
        }

        $consumer = $this->consumerFactory->get($consumerName, $batchSize);
        $consumer->process($numberOfMessages);
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Saves pid of current process to file
     *
     * @param string $pidFilePath The path to file with pid
     */
    private function savePid($pidFilePath)
    {
        $file = $this->writeFactory->create($pidFilePath, DriverPool::FILE, 'w');
        $file->write(posix_getpid());
        $file->close();
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
            self::OPTION_BATCH_SIZE,
            null,
            InputOption::VALUE_REQUIRED,
            'The number of messages per batch. Applicable for the batch consumer only.'
        );
        $this->addOption(
            self::OPTION_AREACODE,
            null,
            InputOption::VALUE_REQUIRED,
            'The preferred area (global, adminhtml, etc...) '
            . 'default is global.'
        );
        $this->addOption(
            self::PID_FILE_PATH,
            null,
            InputOption::VALUE_REQUIRED,
            'The file path for saving PID'
        );
        $this->setHelp(
            <<<HELP
This command starts MessageQueue consumer by its name.

To start consumer which will process all queued messages and terminate execution:

    <comment>%command.full_name% someConsumer</comment>

To specify the number of messages which should be processed by consumer before its termination:

    <comment>%command.full_name% someConsumer --max-messages=50</comment>

To specify the number of messages per batch for the batch consumer:

    <comment>%command.full_name% someConsumer --batch-size=500</comment>

To specify the preferred area:

    <comment>%command.full_name% someConsumer --area-code='adminhtml'</comment>

To save PID enter path:

    <comment>%command.full_name% someConsumer --pid-file-path='/var/someConsumer.pid'</comment>
HELP
        );
        parent::configure();
    }
}
