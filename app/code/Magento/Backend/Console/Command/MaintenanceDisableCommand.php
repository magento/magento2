<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

/**
 * Command for disabling maintenance mode
 */
class MaintenanceDisableCommand extends AbstractMaintenanceCommand
{
    public const NAME = 'maintenance:disable';

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)->setDescription('Disables maintenance mode');

        parent::configure();
    }

    /**
     * Disable maintenance mode
     *
     * @return bool
     */
    protected function isEnable(): bool
    {
        return false;
    }

    /**
     * Get disabled maintenance mode display string
     *
     * @return string
     */
    protected function getDisplayString(): string
    {
        return '<info>Disabled maintenance mode</info>';
    }

    /**
     * Return if IP addresses effective for maintenance mode were set
     *
     * @return bool
     */
    public function isSetAddressInfo(): bool
    {
        return count($this->maintenanceMode->getAddressInfo()) > 0;
    }
}
