<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * An abstract class for Magento CLI commands that support bootstrap initialization parameters.
 *
 * Provides the --magento-init-params option to customize Magento initialization.
 *
 * @api
 */
abstract class AbstractSetupCommand extends Command
{
    /**
     * A CLI parameter for injecting bootstrap variables
     */
    public const BOOTSTRAP_PARAM = 'magento-init-params';

    /**
     * Initialize command with Magento bootstrap parameters option
     *
     * @return void
     */
    protected function configure()
    {
        $this->addOption(
            self::BOOTSTRAP_PARAM,
            null,
            InputOption::VALUE_REQUIRED,
            'Add to any command to customize Magento initialization parameters' . PHP_EOL .
            'For example: `MAGE_MODE=developer&MAGE_DIRS[base][path]' .
            '=/var/www/example.com&MAGE_DIRS[cache][path]=/var/tmp/cache`'
        );
    }
}
