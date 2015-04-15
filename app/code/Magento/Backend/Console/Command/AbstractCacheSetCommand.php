<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCacheSetCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_KEY_TYPES,
            InputArgument::IS_ARRAY,
            'list of cache types, space separated If omitted, all caches will be affected'
        );
        $this->addOption(
            self::INPUT_KEY_ALL,
            null,
            InputOption::VALUE_NONE,
            'all cache types'
        );
    }

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
        if ($input->getOption(self::INPUT_KEY_ALL)) {
            $types = $this->cacheManager->getAvailableTypes();
        } else {
            $types = $this->getRequestedTypes($input);
        }
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
