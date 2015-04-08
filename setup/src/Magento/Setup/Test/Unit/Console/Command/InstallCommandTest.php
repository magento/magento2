<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InstallCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Setup\Model\AdminAccount;
use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Framework\Config\ConfigOptionsList as SetupConfigOptionsList;
use Magento\Setup\Console\Command\InstallUserConfigurationCommand;

class InstallCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $input = [
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_HOST => 'localhost',
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_NAME => 'magento',
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_USER => 'root',
            '--' . BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin',
            '--' . InstallUserConfigurationCommand::INPUT_BASE_URL => 'http://127.0.0.1/magento2ce/',
            '--' . InstallUserConfigurationCommand::INPUT_LANGUAGE => 'en_US',
            '--' . InstallUserConfigurationCommand::INPUT_TIMEZONE => 'America/Chicago',
            '--' . InstallUserConfigurationCommand::INPUT_CURRENCY => 'USD',
            AdminAccount::KEY_USER => 'user',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_EMAIL => 'test@test.com',
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
        ];

        $option1 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(SetupConfigOptionsList::INPUT_KEY_DB_HOST));
        $option2 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(SetupConfigOptionsList::INPUT_KEY_DB_NAME));
        $option3 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(SetupConfigOptionsList::INPUT_KEY_DB_USER));
        $option4 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME));
        $configModel = $this->getMock('Magento\Setup\Model\ConfigModel', [], [], '', false);
        $configModel
            ->expects($this->exactly(2))
            ->method('getAvailableOptions')
            ->will($this->returnValue([$option1, $option2, $option3, $option4]));

        $option5 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option5
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallUserConfigurationCommand::INPUT_BASE_URL));
        $option6 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option6
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallUserConfigurationCommand::INPUT_LANGUAGE));
        $option7 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option7
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallUserConfigurationCommand::INPUT_TIMEZONE));
        $option8 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option8
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallUserConfigurationCommand::INPUT_CURRENCY));
        $userConfig = $this->getMock(
            'Magento\Setup\Console\Command\InstallUserConfigurationCommand',
            [],
            [],
            '',
            false
        );
        $userConfig
            ->expects($this->once())
            ->method('getOptionsList')
            ->will($this->returnValue([$option5, $option6, $option7, $option8]));

        $argument1 = $this->getMock('Symfony\Component\Console\Input\InputArgument', [], [], '', false);
        $argument1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_USER));
        $argument2 = $this->getMock('Symfony\Component\Console\Input\InputArgument', [], [], '', false);
        $argument2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_PASSWORD));
        $argument3 = $this->getMock('Symfony\Component\Console\Input\InputArgument', [], [], '', false);
        $argument3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_EMAIL));
        $argument4 = $this->getMock('Symfony\Component\Console\Input\InputArgument', [], [], '', false);
        $argument4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_FIRST_NAME));
        $argument5 = $this->getMock('Symfony\Component\Console\Input\InputArgument', [], [], '', false);
        $argument5
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_LAST_NAME));
        $adminUser = $this->getMock('Magento\Setup\Console\Command\AdminUserCreateCommand', [], [], '', false);
        $adminUser
            ->expects($this->once())
            ->method('getArgumentsList')
            ->will($this->returnValue([$argument1, $argument2, $argument3, $argument4, $argument5]));

        $installerFactory = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $installer = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $installerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($installer));
        $installer->expects($this->once())->method('install');
        $commandTester = new CommandTester(new InstallCommand(
            $installerFactory,
            $configModel,
            $userConfig,
            $adminUser
        ));
        $commandTester->execute($input);
    }
}
