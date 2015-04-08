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
use Magento\Setup\Console\Command\InstallStoreConfigurationCommand;

class InstallCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $input = [
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_HOST => 'localhost',
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_NAME => 'magento',
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_USER => 'root',
            '--' . BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin',
            '--' . InstallStoreConfigurationCommand::INPUT_BASE_URL => 'http://127.0.0.1/magento2ce/',
            '--' . InstallStoreConfigurationCommand::INPUT_LANGUAGE => 'en_US',
            '--' . InstallStoreConfigurationCommand::INPUT_TIMEZONE => 'America/Chicago',
            '--' . InstallStoreConfigurationCommand::INPUT_CURRENCY => 'USD',
            '--' . AdminAccount::KEY_USER => 'user',
            '--' . AdminAccount::KEY_PASSWORD => '123123q',
            '--' . AdminAccount::KEY_EMAIL => 'test@test.com',
            '--' . AdminAccount::KEY_FIRST_NAME => 'John',
            '--' . AdminAccount::KEY_LAST_NAME => 'Doe',
        ];

        $configModel = $this->getMock('Magento\Setup\Model\ConfigModel', [], [], '', false);
        $configModel
            ->expects($this->exactly(2))
            ->method('getAvailableOptions')
            ->will($this->returnValue($this->getOptionsListDeployConfig()));
        $configModel
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue([]));

        $userConfig = $this->getMock(
            'Magento\Setup\Console\Command\InstallStoreConfigurationCommand',
            [],
            [],
            '',
            false
        );
        $userConfig
            ->expects($this->once())
            ->method('getOptionsList')
            ->will($this->returnValue($this->getOptionsListUserConfig()));

        $adminUser = $this->getMock('Magento\Setup\Console\Command\AdminUserCreateCommand', [], [], '', false);
        $adminUser
            ->expects($this->once())
            ->method('getOptionsList')
            ->will($this->returnValue($this->getOptionsListAdminUser()));
        $adminUser
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue([]));

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

    /**
     * Get list of options for deployment configuration
     *
     * @return array
     */
    private function getOptionsListDeployConfig()
    {
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
        return [$option1, $option2, $option3, $option4];
    }

    /**
     * Get list of options for user configuration
     *
     * @return array
     */
    private function getOptionsListUserConfig()
    {
        $option1 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallStoreConfigurationCommand::INPUT_BASE_URL));
        $option2 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallStoreConfigurationCommand::INPUT_LANGUAGE));
        $option3 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallStoreConfigurationCommand::INPUT_TIMEZONE));
        $option4 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(InstallStoreConfigurationCommand::INPUT_CURRENCY));
        return [$option1, $option2, $option3, $option4];
    }

    /**
     * Get list of options for admin user
     *
     * @return array
     */
    private function getOptionsListAdminUser()
    {
        $option1 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_USER));
        $option2 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_PASSWORD));
        $option3 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_EMAIL));
        $option4 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_FIRST_NAME));
        $option5 = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option5
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_LAST_NAME));
        return [$option1, $option2, $option3, $option4, $option5];
    }
}
