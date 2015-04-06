<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

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
            'magento_init_params',
            null,
            InputOption::VALUE_REQUIRED,
            'Magento initialization parameters'
        );
    }
}
