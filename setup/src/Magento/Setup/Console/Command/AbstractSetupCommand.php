<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * An abstract class for all Magento Setup command.
 * It adds InitParamListener's magento-init-params option to all setup command.
 */
abstract class AbstractSetupCommand extends Command
{
    /**
     * Initialize basic Magento Setup command
     *
     * @return void
     */
    protected function configure()
    {
        $this->addOption(
            InitParamListener::BOOTSTRAP_PARAM,
            null,
            InputOption::VALUE_REQUIRED,
            'Add to any command to customize Magento initialization parameters' . PHP_EOL .
            'For example: "MAGE_MODE=developer&MAGE_DIRS[base][path]' .
            '=/var/www/example.com&MAGE_DIRS[cache][path]=/var/tmp/cache"'
        );
    }
}
