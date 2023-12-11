<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\RemoteStorage\Model\Synchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\RemoteStorage\Model\Config;

/**
 * Synchronizes local storage with remote storage.
 */
class RemoteStorageSynchronizeCommand extends Command
{
    private const NAME = 'remote-storage:sync';

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Synchronizer $synchronizer
     * @param Config $config
     */
    public function __construct(
        Synchronizer $synchronizer,
        Config $config
    ) {
        $this->synchronizer = $synchronizer;
        $this->config = $config;

        parent::__construct(self::NAME);
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDescription('Synchronize media files with remote storage.');
    }

    /**
     * Run synchronization.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->config->isEnabled()) {
            $output->writeln('<error>Remote storage is not enabled.</error>');

            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Uploading media files to remote storage.</info>');

        foreach ($this->synchronizer->execute() as $file) {
            $output->writeln('- ' . $file);
        }

        $output->writeln('<info>End of upload.</info>');

        return Cli::RETURN_SUCCESS;
    }
}
