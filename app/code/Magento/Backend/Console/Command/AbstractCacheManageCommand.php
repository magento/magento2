<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @api
 * @since 100.0.2
 */
abstract class AbstractCacheManageCommand extends AbstractCacheCommand
{
    /**
     * Input argument types
     */
    const INPUT_KEY_TYPES = 'types';

    const EXCLUDE_KEY_TYPES = 'exclude';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->addOption(self::EXCLUDE_KEY_TYPES, '-e', InputOption::VALUE_OPTIONAL);
        $this->addArgument(
            self::INPUT_KEY_TYPES,
            InputArgument::IS_ARRAY,
            'Space-separated list of cache types or omit to apply to all cache types.'
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
            $cacheTypes = $this->cacheManager->getAvailableTypes();
            if ($input->getOption(self::EXCLUDE_KEY_TYPES)) {
                unset($cacheTypes[array_search($input->getOption(self::EXCLUDE_KEY_TYPES), $cacheTypes)]);
                $cacheTypes = array_values($cacheTypes);
            }
            return $cacheTypes;
        } else {
            $availableTypes = $this->cacheManager->getAvailableTypes();
            $unsupportedTypes = array_diff($requestedTypes, $availableTypes);
            if ($unsupportedTypes) {
                throw new \InvalidArgumentException(
                    "The following requested cache types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'." . PHP_EOL . 'Supported types: ' . join(", ", $availableTypes)
                );
            }
            if ($input->getOption(self::EXCLUDE_KEY_TYPES)) {
                unset($availableTypes[array_search($input->getOption(self::EXCLUDE_KEY_TYPES), $availableTypes)]);
                $availableTypes = array_values($availableTypes);
            }
            return array_values(array_intersect($availableTypes, $requestedTypes));
        }
    }
}
