<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:status');
        $this->setDescription('Checks cache status');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Current status:');
        foreach ($this->cacheManager->getStatus() as $cache => $status) {
            $output->writeln(sprintf('%30s: %d', $cache, $status));
        }
    }
}
