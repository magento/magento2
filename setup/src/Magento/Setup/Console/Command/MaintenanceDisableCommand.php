<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

/**
 * Command for disabling maintenance mode
 * @since 2.0.0
 */
class MaintenanceDisableCommand extends AbstractMaintenanceCommand
{
    /**
     * Initialization of the command
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function isEnable()
    {
        return false;
    }

    /**
     * Get disabled maintenance mode display string
     *
     * @return string
     * @since 2.0.0
     */
    protected function getDisplayString()
    {
        return '<info>Disabled maintenance mode</info>';
    }

    /**
     * Return if IP addresses effective for maintenance mode were set
     *
     * @return bool
     * @since 2.2.0
     */
    public function isSetAddressInfo()
    {
        return count($this->maintenanceMode->getAddressInfo()) > 0;
    }
}
