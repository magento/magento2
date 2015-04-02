<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

/**
 * Command for enabling maintenance mode
 */
class MaintenanceEnableCommand extends AbstractMaintenanceCommand
{
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('maintenance:enable')->setDescription('Enable maintenance mode');
        parent::configure();
    }

    protected function isEnable()
    {
        return true;
    }
}
