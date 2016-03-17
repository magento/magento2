<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Console;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for starting MessageQueue consumers.
 */
class ConsumerListCommand extends Command
{
    const COMMAND_QUEUE_CONSUMERS_LIST = 'queue:consumers:list';

    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @param QueueConfig $queueConfig
     * @param string|null $name
     */
    public function __construct(QueueConfig $queueConfig, $name = null)
    {
        $this->queueConfig = $queueConfig;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumers = $this->getConsumers();
        $output->writeln($consumers);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_CONSUMERS_LIST);
        $this->setDescription('List of MessageQueue consumers');
        $this->setHelp(
            <<<HELP
This command shows list of MessageQueue consumers.
HELP
        );
        parent::configure();
    }

    /**
     * @return string[]
     */
    private function getConsumers()
    {
        return $this->queueConfig->getConsumerNames();
    }
}
