<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Console;

use Magento\Framework\Console\Cli;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for put poison pill for MessageQueue consumers.
 */
class RestartConsumerCommand extends Command
{
    private const COMMAND_QUEUE_CONSUMERS_RESTART = 'queue:consumers:restart';

    /**
     * @var PoisonPillPutInterface
     */
    private $poisonPillPut;

    /**
     * @param PoisonPillPutInterface $poisonPillPut
     * @param string|null $name
     */
    public function __construct(PoisonPillPutInterface $poisonPillPut, $name = null)
    {
        parent::__construct($name);
        $this->poisonPillPut = $poisonPillPut;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->poisonPillPut->put();
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_CONSUMERS_RESTART);
        $this->setDescription('Restart MessageQueue consumers');
        $this->setHelp(
            <<<HELP
Command put poison pill for MessageQueue consumers and force to restart them after next status check.
HELP
        );
        parent::configure();
    }
}
