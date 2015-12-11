<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command\Setup\DbSchema;

use Magento\Setup\Test\Unit\Console\Command\Setup\DbData\UpgradeCommandTest as DbDataUpgradeCommandTest;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Console\Command\Setup\DbSchema\UpgradeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class UpgradeCommandTest extends DbDataUpgradeCommandTest
{
    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->will($this->returnValue(true));
        $installer = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $this->installerFactory->expects($this->once())->method('create')->will($this->returnValue($installer));
        $installer->expects($this->once())->method('installSchema');

        $commandTester = new CommandTester(
            new UpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
    }

    public function testExecuteNoConfig()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->will($this->returnValue(false));
        $this->installerFactory->expects($this->never())->method('create');

        $commandTester = new CommandTester(
            new UpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $commandTester->getDisplay()
        );
    }
}
