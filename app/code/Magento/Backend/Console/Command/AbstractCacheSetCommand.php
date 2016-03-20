<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCacheSetCommand extends AbstractCacheManageCommand
{
    /**
     * Is enable cache or not
     *
     * @return bool
     */
    abstract protected function isEnable();

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isEnable = $this->isEnable();
        $types = $this->getRequestedTypes($input);
        $changedTypes = $this->cacheManager->setEnabled($types, $isEnable);
        if ($changedTypes) {
            $output->writeln('Changed cache status:');
            foreach ($changedTypes as $type) {
                $output->writeln(sprintf('%30s: %d -> %d', $type, !$isEnable, $isEnable));
            }
        } else {
            $output->writeln('There is nothing to change in cache status');
        }
        if (!empty($changedTypes) && $isEnable) {
            $this->cacheManager->clean($changedTypes);
            $output->writeln('Cleaned cache types:');
            $output->writeln(join(PHP_EOL, $changedTypes));
        }
    }
}
