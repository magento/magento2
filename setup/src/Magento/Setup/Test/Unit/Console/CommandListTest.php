<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console;

use Magento\Setup\Console\CommandList;

class CommandListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Console\CommandList
     */
    private $commandList;

     /**
      * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceManager
      */
    private $serviceManager;

    public function setUp()
    {
        $this->serviceManager = $this->getMock('\Zend\ServiceManager\ServiceManager', [], [], '', false);
        $this->commandList = new CommandList($this->serviceManager);
    }

    public function testGetCommands()
    {
        $commands = [
            'Magento\Setup\Console\Command\AdminUserCreateCommand',
            'Magento\Setup\Console\Command\ConfigSetCommand',
            'Magento\Setup\Console\Command\DbDataUpgradeCommand',
            'Magento\Setup\Console\Command\DbSchemaUpgradeCommand',
            'Magento\Setup\Console\Command\DbStatusCommand',
            'Magento\Setup\Console\Command\InfoCurrencyListCommand',
            'Magento\Setup\Console\Command\InfoLanguageListCommand',
            'Magento\Setup\Console\Command\InfoTimezoneListCommand',
            'Magento\Setup\Console\Command\InstallCommand',
            'Magento\Setup\Console\Command\InstallStoreConfigurationCommand',
            'Magento\Setup\Console\Command\ModuleEnableCommand',
            'Magento\Setup\Console\Command\ModuleDisableCommand',
            'Magento\Setup\Console\Command\ModuleStatusCommand',
            'Magento\Setup\Console\Command\MaintenanceAllowIpsCommand',
            'Magento\Setup\Console\Command\MaintenanceDisableCommand',
            'Magento\Setup\Console\Command\MaintenanceEnableCommand',
            'Magento\Setup\Console\Command\MaintenanceStatusCommand',
            'Magento\Setup\Console\Command\UpgradeCommand',
            'Magento\Setup\Console\Command\UninstallCommand',
        ];
        $index = 0;
        foreach ($commands as $command) {
            $this->serviceManager->expects($this->at($index++))
                ->method('create')
                ->with($command);
        }

        $this->commandList->getCommands();
    }
}
