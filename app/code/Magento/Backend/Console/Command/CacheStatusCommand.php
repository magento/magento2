<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking cache status
 *
 * @api
 * @since 100.0.2
 */
class CacheStatusCommand extends AbstractCacheCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('cache:status');
        $this->setDescription('Checks cache status');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Current status:');
        foreach ($this->cacheManager->getStatus() as $cache => $status) {
            $output->writeln(sprintf('%30s: %d', $cache, $status));
        }

        return Cli::RETURN_SUCCESS;
    }
}
