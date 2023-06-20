<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Console\Command;

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
    protected function configure(): void
    {
        $this->setName('maintenance:enable')->setDescription('Enables maintenance mode');

        parent::configure();
    }

    /**
     * Enable maintenance mode
     *
     * @return bool
     */
    protected function isEnable(): bool
    {
        return true;
    }

    /**
     * Get enabled maintenance mode display string
     *
     * @return string
     */
    protected function getDisplayString(): string
    {
        return '<info>Enabled maintenance mode</info>';
    }
}
