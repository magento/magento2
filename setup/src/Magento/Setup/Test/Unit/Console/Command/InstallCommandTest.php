<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InstallCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Setup\Model\AdminAccount;
use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Framework\Config\ConfigOptionsListConstants as SetupConfigOptionsList;
use Magento\Setup\Model\StoreConfigurationDataMapper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private $input;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InstallCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\InstallerFactory
     */
    private $installerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Installer
     */
    private $installer;

    public function setUp()
    {
        $this->input = [
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_HOST => 'localhost',
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_NAME => 'magento',
            '--' . SetupConfigOptionsList::INPUT_KEY_DB_USER => 'root',
            '--' . BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin',
            '--' . StoreConfigurationDataMapper::KEY_BASE_URL => 'http://127.0.0.1/magento2ce/',
            '--' . StoreConfigurationDataMapper::KEY_LANGUAGE => 'en_US',
            '--' . StoreConfigurationDataMapper::KEY_TIMEZONE => 'America/Chicago',
            '--' . StoreConfigurationDataMapper::KEY_CURRENCY => 'USD',
            '--' . AdminAccount::KEY_USER => 'user',
            '--' . AdminAccount::KEY_PASSWORD => '123123q',
            '--' . AdminAccount::KEY_EMAIL => 'test@test.com',
            '--' . AdminAccount::KEY_FIRST_NAME => 'John',
            '--' . AdminAccount::KEY_LAST_NAME => 'Doe',
        ];

        $configModel = $this->createMock(\Magento\Setup\Model\ConfigModel::class);
        $configModel
            ->expects($this->exactly(2))
            ->method('getAvailableOptions')
            ->will($this->returnValue($this->getOptionsListDeployConfig()));
        $configModel
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue([]));

        $userConfig = $this->createMock(\Magento\Setup\Console\Command\InstallStoreConfigurationCommand::class);
        $userConfig
            ->expects($this->once())
            ->method('getOptionsList')
            ->will($this->returnValue($this->getOptionsListUserConfig()));
        $userConfig
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue([]));

        $adminUser = $this->createMock(\Magento\Setup\Console\Command\AdminUserCreateCommand::class);
        $adminUser
            ->expects($this->once())
            ->method('getOptionsList')
            ->will($this->returnValue($this->getOptionsListAdminUser()));
        $adminUser
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue([]));

        $this->installerFactory = $this->createMock(\Magento\Setup\Model\InstallerFactory::class);
        $this->installer = $this->createMock(\Magento\Setup\Model\Installer::class);
        $this->command = new InstallCommand(
            $this->installerFactory,
            $configModel,
            $userConfig,
            $adminUser
        );
    }

    public function testExecute()
    {
        $this->installerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->installer));
        $this->installer->expects($this->once())->method('install');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->input);
    }

    /**
     * Get list of options for deployment configuration
     *
     * @return array
     */
    private function getOptionsListDeployConfig()
    {
        $option1 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(SetupConfigOptionsList::INPUT_KEY_DB_HOST));
        $option2 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(SetupConfigOptionsList::INPUT_KEY_DB_NAME));
        $option3 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(SetupConfigOptionsList::INPUT_KEY_DB_USER));
        $option4 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
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
        $option1 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(StoreConfigurationDataMapper::KEY_BASE_URL));
        $option2 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(StoreConfigurationDataMapper::KEY_LANGUAGE));
        $option3 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(StoreConfigurationDataMapper::KEY_TIMEZONE));
        $option4 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(StoreConfigurationDataMapper::KEY_CURRENCY));
        return [$option1, $option2, $option3, $option4];
    }

    /**
     * Get list of options for admin user
     *
     * @return array
     */
    private function getOptionsListAdminUser()
    {
        $option1 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_USER));
        $option2 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_PASSWORD));
        $option3 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_EMAIL));
        $option4 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_FIRST_NAME));
        $option5 = $this->createMock(\Magento\Framework\Setup\Option\TextConfigOption::class);
        $option5
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(AdminAccount::KEY_LAST_NAME));
        return [$option1, $option2, $option3, $option4, $option5];
    }

    /**
     * Test install command with valid sales_order_increment_prefix value
     *
     * @dataProvider validateDataProvider
     * @param $prefixValue
     */
    public function testValidate($prefixValue)
    {
        $this->installerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->installer));
        $this->installer->expects($this->once())->method('install');
        $this->input['--' . InstallCommand::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX] = $prefixValue;

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->input);
    }

    /**
     * Test install command with invalid sales_order_increment_prefix value
     *
     * @expectedException \InvalidArgumentException
     * @dataProvider validateWithExceptionDataProvider
     * @param $prefixValue
     */
    public function testValidateWithException($prefixValue)
    {
        $this->installerFactory->expects($this->never())
            ->method('create')
            ->will($this->returnValue($this->installer));
        $this->installer->expects($this->never())->method('install');
        $this->input['--' . InstallCommand::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX] = $prefixValue;

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->input);
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'without option' => ['', ''],
            'normal case'    => ['abcde', ''],
            '20 chars'       => ['12345678901234567890', '']
        ];
    }

    /**
     * @return array
     */
    public function validateWithExceptionDataProvider()
    {
        return [
            ['123456789012345678901', 'InvalidArgumentException'],
            ['abcdefghijk12345678fdgsdfgsdfgsdfsgsdfg90abcdefgdfddgsdfg', 'InvalidArgumentException']
        ];
    }
}
