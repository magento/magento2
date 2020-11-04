<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\Config\ConfigOptionsListConstants as SetupConfigOptionsList;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Console\Command\AdminUserCreateCommand;
use Magento\Setup\Console\Command\InstallCommand;
use Magento\Setup\Console\Command\InstallStoreConfigurationCommand;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\ConfigModel;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\StoreConfigurationDataMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Setup\Model\SearchConfigOptionsList;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallCommandTest extends TestCase
{
    /**
     * @var array
     */
    private $input;

    /**
     * @var MockObject|InstallCommand
     */
    private $command;

    /**
     * @var MockObject|InstallerFactory
     */
    private $installerFactory;

    /**
     * @var MockObject|Installer
     */
    private $installer;

    /**
     * @var Application|MockObject
     */
    private $applicationMock;

    /**
     * @var HelperSet|MockObject
     */
    private $helperSetMock;

    /**
     * @var InputDefinition|MockObject
     */
    private $definitionMock;

    /**
     * @var ConfigImportCommand|MockObject
     */
    private $configImportMock;

    /**
     * @var AdminUserCreateCommand|MockObject
     */
    private $adminUserMock;

    protected function setUp(): void
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
        ];

        $configModel = $this->createMock(ConfigModel::class);
        $configModel
            ->expects($this->exactly(2))
            ->method('getAvailableOptions')
            ->willReturn($this->getOptionsListDeployConfig());
        $configModel
            ->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $userConfig = $this->createMock(InstallStoreConfigurationCommand::class);
        $userConfig
            ->expects($this->once())
            ->method('getOptionsList')
            ->willReturn($this->getOptionsListUserConfig());
        $userConfig
            ->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->adminUserMock = $this->createMock(AdminUserCreateCommand::class);
        $this->adminUserMock
            ->expects($this->once())
            ->method('getOptionsList')
            ->willReturn($this->getOptionsListAdminUser());

        $searchConfigOptionsList = new SearchConfigOptionsList();
        $this->installerFactory = $this->createMock(InstallerFactory::class);
        $this->installer = $this->createMock(Installer::class);
        $this->applicationMock = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperSetMock = $this->getMockBuilder(HelperSet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->definitionMock = $this->getMockBuilder(InputDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configImportMock = $this->getMockBuilder(ConfigImportCommand::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->applicationMock->expects($this->any())
            ->method('getHelperSet')
            ->willReturn($this->helperSetMock);
        $this->applicationMock->expects($this->any())
            ->method('getDefinition')
            ->willReturn($this->definitionMock);
        $this->definitionMock->expects($this->any())
            ->method('getOptions')
            ->willReturn([]);
        $this->applicationMock->expects($this->any())
            ->method('find')
            ->with(ConfigImportCommand::COMMAND_NAME)
            ->willReturn($this->configImportMock);

        $this->command = new InstallCommand(
            $this->installerFactory,
            $configModel,
            $userConfig,
            $this->adminUserMock,
            $searchConfigOptionsList
        );
        $this->command->setApplication(
            $this->applicationMock
        );
    }

    public function testExecute()
    {
        $this->input['--' . AdminAccount::KEY_USER] = 'user';
        $this->input['--' . AdminAccount::KEY_PASSWORD] = '123123q';
        $this->input['--' . AdminAccount::KEY_EMAIL] = 'test@test.com';
        $this->input['--' . AdminAccount::KEY_FIRST_NAME] = 'John';
        $this->input['--' . AdminAccount::KEY_LAST_NAME] = 'Doe';

        $this->adminUserMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn([]);
        $this->installerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->installer);
        $this->installer->expects($this->once())->method('install');
        $this->configImportMock->expects($this->once())
            ->method('run');

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
        $option1 = $this->createMock(TextConfigOption::class);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->willReturn(SetupConfigOptionsList::INPUT_KEY_DB_HOST);
        $option2 = $this->createMock(TextConfigOption::class);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->willReturn(SetupConfigOptionsList::INPUT_KEY_DB_NAME);
        $option3 = $this->createMock(TextConfigOption::class);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->willReturn(SetupConfigOptionsList::INPUT_KEY_DB_USER);
        $option4 = $this->createMock(TextConfigOption::class);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->willReturn(BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME);

        return [$option1, $option2, $option3, $option4];
    }

    /**
     * Get list of options for user configuration
     *
     * @return array
     */
    private function getOptionsListUserConfig()
    {
        $option1 = $this->createMock(TextConfigOption::class);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->willReturn(StoreConfigurationDataMapper::KEY_BASE_URL);
        $option2 = $this->createMock(TextConfigOption::class);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->willReturn(StoreConfigurationDataMapper::KEY_LANGUAGE);
        $option3 = $this->createMock(TextConfigOption::class);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->willReturn(StoreConfigurationDataMapper::KEY_TIMEZONE);
        $option4 = $this->createMock(TextConfigOption::class);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->willReturn(StoreConfigurationDataMapper::KEY_CURRENCY);

        return [$option1, $option2, $option3, $option4];
    }

    /**
     * Get list of options for admin user
     *
     * @return array
     */
    private function getOptionsListAdminUser()
    {
        $option1 = $this->createMock(TextConfigOption::class);
        $option1
            ->expects($this->any())
            ->method('getName')
            ->willReturn(AdminAccount::KEY_USER);
        $option2 = $this->createMock(TextConfigOption::class);
        $option2
            ->expects($this->any())
            ->method('getName')
            ->willReturn(AdminAccount::KEY_PASSWORD);
        $option3 = $this->createMock(TextConfigOption::class);
        $option3
            ->expects($this->any())
            ->method('getName')
            ->willReturn(AdminAccount::KEY_EMAIL);
        $option4 = $this->createMock(TextConfigOption::class);
        $option4
            ->expects($this->any())
            ->method('getName')
            ->willReturn(AdminAccount::KEY_FIRST_NAME);
        $option5 = $this->createMock(TextConfigOption::class);
        $option5
            ->expects($this->any())
            ->method('getName')
            ->willReturn(AdminAccount::KEY_LAST_NAME);

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
        $this->adminUserMock
            ->expects($this->never())
            ->method('validate');
        $this->installerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->installer);
        $this->installer->expects($this->once())->method('install');
        $this->input['--' . InstallCommand::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX] = $prefixValue;

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->input);
    }

    /**
     * Test install command with invalid sales_order_increment_prefix value
     *
     * @dataProvider validateWithExceptionDataProvider
     * @param $prefixValue
     */
    public function testValidateWithException($prefixValue)
    {
        $this->expectException('InvalidArgumentException');
        $this->adminUserMock
            ->expects($this->never())
            ->method('validate');
        $this->installerFactory->expects($this->never())
            ->method('create')
            ->willReturn($this->installer);
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
            'normal case' => ['abcde', ''],
            '20 chars' => ['12345678901234567890', ''],
        ];
    }

    /**
     * @return array
     */
    public function validateWithExceptionDataProvider()
    {
        return [
            ['123456789012345678901', 'InvalidArgumentException'],
            ['abcdefghijk12345678fdgsdfgsdfgsdfsgsdfg90abcdefgdfddgsdfg', 'InvalidArgumentException'],
        ];
    }
}
