<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

use Magento\Framework\App\Cache\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * @api
 * @since 2.0.0
 */
abstract class AbstractCacheCommand extends Command
{
    /**
     * Input option bootsrap
     */
    const INPUT_KEY_BOOTSTRAP = 'bootstrap';

    /**
     * CacheManager
     *
     * @var Manager
     * @since 2.0.0
     */
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param Manager $cacheManager
     * @since 2.0.0
     */
    public function __construct(Manager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
}
