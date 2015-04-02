<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Module\ModuleList;
use Magento\Setup\Console\Command\DbDataUpgradeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DbDataUpgradeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $installerFactory = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $installer = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $consoleLogger = $this->getMock('Magento\Setup\Model\ConsoleLogger', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');

        $objectManagerProvider->expects($this->once())->method('get')->will($this->returnValue($objectManager));
        $objectManager->expects($this->once())->method('create')->will($this->returnValue($consoleLogger));
        $installerFactory->expects($this->once())
            ->method('create')
            ->with($consoleLogger)
            ->will($this->returnValue($installer));
        $installer->expects($this->once())->method('installDataFixtures');

        $commandTester = new CommandTester(new DbDataUpgradeCommand($installerFactory, $objectManagerProvider));
        $commandTester->execute([]);
    }
}
