<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Console;

use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Framework\App\MaintenanceMode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceModeEnablerTest extends TestCase
{
    /**
     * @dataProvider initialAppStateProvider
     */
    public function testSuccessfulTask(bool $maintenanceModeEnabledInitially)
    {
        $maintenanceMode = $this->createMaintenanceMode($maintenanceModeEnabledInitially);
        $enabler = new MaintenanceModeEnabler($maintenanceMode);
        $successTask = function () {
            // do nothing
        };

        $enabler->executeInMaintenanceMode(
            $successTask,
            $this->createOutput(),
            true
        );

        $this->assertEquals(
            $maintenanceModeEnabledInitially,
            $maintenanceMode->isOn(),
            'Initial state is not restored'
        );
    }

    /**
     * @dataProvider initialAppStateProvider
     */
    public function testFailedTaskWithMaintenanceModeOnFailure(bool $maintenanceModeEnabledInitially)
    {
        $maintenanceMode = $this->createMaintenanceMode($maintenanceModeEnabledInitially);
        $enabler = new MaintenanceModeEnabler($maintenanceMode);
        $failedTask = function () {
            throw new \Exception('Woops!');
        };

        try {
            $enabler->executeInMaintenanceMode(
                $failedTask,
                $this->createOutput(),
                true
            );
        } catch (\Exception $e) {
            $this->assertTrue(
                $maintenanceMode->isOn(),
                'Maintenance mode is not active after failure'
            );
        }
    }

    /**
     * @dataProvider initialAppStateProvider
     */
    public function testFailedTaskWithRestoredModeOnFailure(bool $maintenanceModeEnabledInitially)
    {
        $maintenanceMode = $this->createMaintenanceMode($maintenanceModeEnabledInitially);
        $enabler = new MaintenanceModeEnabler($maintenanceMode);
        $failedTask = function () {
            throw new \Exception('Woops!');
        };

        try {
            $enabler->executeInMaintenanceMode(
                $failedTask,
                $this->createOutput(),
                false
            );
        } catch (\Exception $e) {
            $this->assertEquals(
                $maintenanceModeEnabledInitially,
                $maintenanceMode->isOn(),
                'Initial state is not restored'
            );
        }
    }

    /**
     * @return array
     */
    public function initialAppStateProvider()
    {
        return [
            'Maintenance mode disabled initially' => [false],
            'Maintenance mode enabled initially' => [true],
        ];
    }

    /**
     * @param bool $isOn
     * @return MaintenanceMode
     */
    private function createMaintenanceMode(bool $isOn): MaintenanceMode
    {
        $maintenanceMode = $this->getMockBuilder(MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->getMock();

        $maintenanceMode->method('isOn')->willReturnCallback(function () use (&$isOn) {
            return $isOn;
        });
        $maintenanceMode->method('set')->willReturnCallback(function ($newValue) use (&$isOn) {
            $isOn = (bool)$newValue;
            return true;
        });

        return $maintenanceMode;
    }

    /**
     * @return OutputInterface
     */
    private function createOutput(): OutputInterface
    {
        $output = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        return $output;
    }
}
