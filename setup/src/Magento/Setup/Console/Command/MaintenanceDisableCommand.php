<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

/**
 * Command for disabling maintenance mode
 */
class MaintenanceDisableCommand extends AbstractMainTenanceCommand
{
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('maintenance:disable')->setDescription('Disable maintenance mode');
        parent::configure();
    }

    protected function isEnable()
    {
        return false;
    }
}
