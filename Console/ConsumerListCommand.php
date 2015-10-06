<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Console;

use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\MessageQueue\Config\Data as QueueConfig;
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
     * @param $name
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
        $queueConfig = $this->queueConfig->get();
        return array_keys($queueConfig[QueueConfigConverter::CONSUMERS]);
    }
}
