<?php
/**
 * Class MaintenanceMode * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Console;

use Magento\Framework\App\MaintenanceMode;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MaintenanceModeEnabler
 * @package Magento\Framework\App\Console
 */
class MaintenanceModeEnabler
{
    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var bool
     */
    private $skipDisableMaintenanceMode;

    /**
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * Enable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    private function enableMaintenanceMode(OutputInterface $output)
    {
        if ($this->maintenanceMode->isOn()) {
            $this->skipDisableMaintenanceMode = true;
            $output->writeln('<info>Maintenance mode already enabled</info>');
            return;
        }

        $this->maintenanceMode->set(true);
        $this->skipDisableMaintenanceMode = false;
        $output->writeln('<info>Enabling maintenance mode</info>');
    }

    /**
     * Disable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    private function disableMaintenanceMode(OutputInterface $output)
    {
        if ($this->skipDisableMaintenanceMode) {
            $output->writeln('<info>Skipped disabling maintenance mode</info>');
            return;
        }

        $this->maintenanceMode->set(false);
        $output->writeln('<info>Disabling maintenance mode</info>');
    }

    /**
     * Run task in maintenance mode
     *
     * @param callable $task
     * @param OutputInterface $output
     * @param bool $holdMaintenanceOnFailure
     * @return mixed
     * @throws \Throwable if error occurred
     */
    public function executeInMaintenanceMode(callable $task, OutputInterface $output, bool $holdMaintenanceOnFailure)
    {
        $this->enableMaintenanceMode($output);

        try {
            $result = call_user_func($task);
        } catch (\Throwable $e) {
            if (!$holdMaintenanceOnFailure) {
                $this->disableMaintenanceMode($output);
            }
            throw $e;
        }

        $this->disableMaintenanceMode($output);
        return $result;
    }
}
