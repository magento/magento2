<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command\Maintenance;

/**
 * Command for disabling maintenance mode
 */
class DisableCommand extends AbstractMaintenanceCommand
{
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('maintenance:disable')->setDescription('Disables maintenance mode');
        parent::configure();
    }

    /**
     * Disable maintenance mode
     *
     * @return bool
     */
    protected function isEnable()
    {
        return false;
    }

    /**
     * Get disabled maintenance mode display string
     *
     * @return string
     */
    protected function getDisplayString()
    {
        return '<info>Disabled maintenance mode</info>';
    }
}
