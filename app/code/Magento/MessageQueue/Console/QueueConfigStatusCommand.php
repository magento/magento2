<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Console;

use Magento\Framework\Console\Cli;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigChangeDetectorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks whether message-queue topology requires setup:upgrade synchronization.
 */
class QueueConfigStatusCommand extends Command
{
    public const COMMAND_QUEUE_CONFIG_STATUS = 'queue:config:status';

    /**
     * Exit code when setup:upgrade is required to synchronize queue config.
     */
    public const EXIT_CODE_UPGRADE_REQUIRED = 2;

    /**
     * @param QueueConfigChangeDetectorInterface $changeDetector
     * @param string|null $name
     */
    public function __construct(
        private readonly QueueConfigChangeDetectorInterface $changeDetector,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_CONFIG_STATUS);
        $this->setDescription('Checks if queue topology configuration requires upgrade');
        $this->setHelp(
            <<<HELP
This command compares queues declared in topology configuration with the
persisted queue registry and reports when <info>setup:upgrade</info> is needed.
Exit code <info>2</info> means upgrade is required (same convention as setup:db:status).
HELP
        );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $missingQueues = $this->changeDetector->getMissingQueues();
        if ($missingQueues !== []) {
            $output->writeln(
                '<info>Queue topology configuration has changed.</info>'
            );
            $output->writeln(
                '<info>Run setup:upgrade to synchronize queue configuration.</info>'
            );
            $output->writeln(
                '<comment>Missing queues: ' . implode(', ', $missingQueues) . '</comment>'
            );
            return self::EXIT_CODE_UPGRADE_REQUIRED;
        }

        $output->writeln('<info>Queue topology configuration is up to date.</info>');
        return Cli::RETURN_SUCCESS;
    }
}
