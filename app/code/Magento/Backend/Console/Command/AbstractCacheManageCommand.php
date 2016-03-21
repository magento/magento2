<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractCacheManageCommand extends AbstractCacheCommand
{
    /**
     * Input argument types
     */
    const INPUT_KEY_TYPES = 'types';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
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
            return $this->cacheManager->getAvailableTypes();
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
