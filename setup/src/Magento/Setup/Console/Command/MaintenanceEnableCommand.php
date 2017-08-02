<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

/**
 * Command for enabling maintenance mode
 * @since 2.0.0
 */
class MaintenanceEnableCommand extends AbstractMaintenanceCommand
{
    /**
     * Initialization of the command
     *
     * @return void
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('maintenance:enable')->setDescription('Enables maintenance mode');
        parent::configure();
    }

    /**
     * Enable maintenance mode
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isEnable()
    {
        return true;
    }

    /**
     * Get enabled maintenance mode display string
     *
     * @return string
     * @since 2.0.0
     */
    protected function getDisplayString()
    {
        return '<info>Enabled maintenance mode</info>';
    }
}
