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

abstract class AbstractCacheManageCommand extends AbstractCacheCommand
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
     * Perform a cache management action on cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    abstract protected function performAction(array $cacheTypes);

    /**
     * Get display message
     *
     * @return string
     */
    abstract protected function getDisplayMessage();

    /**
     * Perform cache management action
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::INPUT_KEY_ALL)) {
            $types = $this->cacheManager->getAvailableTypes();
        } else {
            $types = $this->getRequestedTypes($input);
        }
        $this->performAction($types);
        $output->writeln($this->getDisplayMessage());
        $output->writeln(join(PHP_EOL, $types));
    }
}
