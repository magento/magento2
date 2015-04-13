<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Magento\Framework\App\Cache\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCacheCommand extends Command
{
    /**
     * Input argument types
     */
    const INPUT_KEY_TYPES = 'types';

    /**
     * Input option bootsrap
     */
    const INPUT_KEY_BOOTSTRAP = 'bootstrap';

    /**
     * CacheManager
     *
     * @var Manager
     */
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param Manager $cacheManager
     */
    public function __construct(Manager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            self::INPUT_KEY_BOOTSTRAP,
            null,
            InputOption::VALUE_REQUIRED,
            'add or override parameters of the bootstrap'
        );
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
        $availableTypes = $this->cacheManager->getAvailableTypes();
        if (empty($requestedTypes)) {
            return [];
        } else {
            $unsupportedTypes = array_diff($requestedTypes, $availableTypes);
            if ($unsupportedTypes) {
                throw new \InvalidArgumentException(
                    "The following requested cache types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'.\nSupported types: " . join(", ", $availableTypes) . ""
                );
            }
            return array_values(array_intersect($availableTypes, $requestedTypes));
        }
    }
}
