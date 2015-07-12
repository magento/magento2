<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCacheManageCommand extends AbstractCacheCommand
{
    /**
     * Input argument types
     */
    const INPUT_KEY_TYPES = 'types';

    /**
     * Input key all
     */
    const INPUT_KEY_ALL = 'all';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_KEY_TYPES,
            InputArgument::IS_ARRAY,
            'List of cache types, space separated. If omitted, all caches will be affected'
        );
        $this->addOption(
            self::INPUT_KEY_ALL,
            null,
            InputOption::VALUE_NONE,
            'All cache types'
        );
        parent::configure();
    }


    /**
     * Get requested cache types
     *
     * @param InputInterface $input
     * @return array
     */
    protected function getRequestedTypes(InputInterface $input)
    {
        $requestedTypes = [];
        if ($input->getArgument(self::INPUT_KEY_TYPES)) {
            $requestedTypes = $input->getArgument(self::INPUT_KEY_TYPES);
            $requestedTypes = array_filter(array_map('trim', $requestedTypes), 'strlen');
        }
        if (empty($requestedTypes)) {
            return [];
        } else {
            $availableTypes = $this->cacheManager->getAvailableTypes();
            $unsupportedTypes = array_diff($requestedTypes, $availableTypes);
            if ($unsupportedTypes) {
                throw new \InvalidArgumentException(
                    "The following requested cache types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'." . PHP_EOL . 'Supported types: ' . join(", ", $availableTypes)
                );
            }
            return array_values(array_intersect($availableTypes, $requestedTypes));
        }
    }
}
