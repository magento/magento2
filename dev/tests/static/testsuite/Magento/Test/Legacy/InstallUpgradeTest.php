<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Legacy;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\Utility\AggregateInvoker;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Tests to find obsolete install/upgrade schema/data scripts.
 */
class InstallUpgradeTest extends \PHPUnit\Framework\TestCase
{
    public function testForOldInstallUpgradeScripts()
    {
        $scriptPattern = [];
        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $scriptPattern[] = $moduleDir . '/sql';
            $scriptPattern[] = $moduleDir . '/data';
            $scriptPattern[] = $moduleDir . '/Setup';
        }
        $invoker = new AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $this->assertStringStartsNotWith(
                    'install-',
                    basename($file),
                    'Install scripts are obsolete. '
                    . 'Please use declarative schema approach in module\'s etc/db_schema.xml file'
                );
                $this->assertStringStartsNotWith(
                    'InstallSchema',
                    basename($file),
                    'InstallSchema objects are obsolete. '
                    . 'Please use declarative schema approach in module\'s etc/db_schema.xml file'
                );
                $this->assertStringStartsNotWith(
                    'InstallData',
                    basename($file),
                    'InstallData objects are obsolete. '
                    . 'Please use data patches approach in module\'s Setup/Patch/Data dir'
                );
                $this->assertStringStartsNotWith(
                    'data-install-',
                    basename($file),
                    'Install scripts are obsolete. Please create class InstallData in module\'s Setup folder'
                );
                $this->assertStringStartsNotWith(
                    'upgrade-',
                    basename($file),
                    'Upgrade scripts are obsolete. '
                    . 'Please use declarative schema approach in module\'s etc/db_schema.xml file'
                );
                $this->assertStringStartsNotWith(
                    'UpgradeSchema',
                    basename($file),
                    'UpgradeSchema scripts are obsolete. '
                    . 'Please use declarative schema approach in module\'s etc/db_schema.xml file'
                );
                $this->assertStringStartsNotWith(
                    'UpgradeData',
                    basename($file),
                    'UpgradeSchema scripts are obsolete. '
                    . 'Please use data patches approach in module\'s Setup/Patch/Data dir'
                );
                $this->assertStringStartsNotWith(
                    'data-upgrade-',
                    basename($file),
                    'Upgrade scripts are obsolete. '
                    . 'Please use data patches approach in module\'s Setup/Patch/Data dir'
                );
                $this->assertStringStartsNotWith(
                    'recurring',
                    basename($file),
                    'Recurring scripts are obsolete. Please create class Recurring in module\'s Setup folder'
                );
                if (preg_match('/.*\/(sql\/|data\/)/', dirname($file))) {
                    $this->fail(
                        "Invalid directory:\n"
                        . "- Create an data patch within module's Setup/Patch/Data folder for data upgrades.\n"
                        . "- Use declarative schema approach in module's etc/db_schema.xml file for schema changes."
                    );
                }
            },
            $this->convertArray(
                Files::init()->getFiles($scriptPattern, '*.php')
            )
        );
    }

    /**
     * Converts from string array to array of arrays.
     *
     * @param array $stringArray
     * @return array
     */
    private function convertArray($stringArray)
    {
        $array = [];
        foreach ($stringArray as $item) {
            $array[] = [$item];
        }
        return $array;
    }
}
