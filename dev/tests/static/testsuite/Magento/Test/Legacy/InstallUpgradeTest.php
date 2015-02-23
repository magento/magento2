<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests to find obsolete install/upgrade schema/data scripts
 */
namespace Magento\Test\Legacy;

class InstallUpgradeTest extends \PHPUnit_Framework_TestCase
{
    public function testForOldInstallUpgradeScripts()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $dirName = dirname(dirname($file));
                $this->assertStringStartsNotWith(
                    'install',
                    basename($file),
                    'Install scripts are obsolete. Please create class InstallSchema.php or InstallData.php '
                    . 'in ' . $dirName . '/Setup folder'
                );
                $this->assertStringStartsNotWith(
                    'upgrade',
                    basename($file),
                    'Upgrade scripts are obsolete. Please create class UpgradeSchema.php or UpgradeData.php '
                    . 'in ' . $dirName . '/Setup folder'
                );
                $this->assertStringStartsNotWith(
                    'recurring',
                    basename($file),
                    'Recurring scripts are obsolete. Please create class Recurring.php '
                    . 'in ' . $dirName . '/Setup folder'
                );
                $this->assertStringEndsNotWith(
                    '/sql',
                    dirname($file),
                    'Invalid directory. Please convert sql scripts to a class within ' . $dirName . '/Setup folder'
                );
                $this->assertStringEndsNotWith(
                    '/data',
                    dirname($file),
                    'Invalid directory. Please convert data scripts to a class within ' . $dirName . '/Setup folder'
                );
            },
            \Magento\Framework\Test\Utility\Files::init()->getPhpFiles(true, false, false)
        );
    }
}
