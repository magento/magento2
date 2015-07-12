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
    /**
     * @var \Magento\Setup\Model\InstallerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $installerFactory;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentConfig;

    protected function setup()
    {
        $this->installerFactory = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->will($this->returnValue(true));
        $installer = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $this->installerFactory->expects($this->once())->method('create')->will($this->returnValue($installer));
        $installer->expects($this->once())->method('installDataFixtures');

        $commandTester = new CommandTester(
            new DbDataUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
    }

    public function testExecuteNoConfig()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->will($this->returnValue(false));
        $this->installerFactory->expects($this->never())->method('create');

        $commandTester = new CommandTester(
            new DbDataUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $commandTester->getDisplay()
        );
    }
}
