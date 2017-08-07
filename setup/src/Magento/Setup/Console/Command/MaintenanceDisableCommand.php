<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

/**
 * Command for disabling maintenance mode
 */
class MaintenanceDisableCommand extends AbstractMaintenanceCommand
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
