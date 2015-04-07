<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Console\Command\AdminUserCreateCommand;
use Symfony\Component\Console\Tester\CommandTester;

class AdminUserCreateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\InstallerFactory
     */
    private $installerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AdminUserCreateCommand
     */
    private $command;

    public function setUp()
    {
        $this->installerFactoryMock = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $this->command = new AdminUserCreateCommand($this->installerFactoryMock);
    }

    public function testExecute()
    {
        $arguments = [
            AdminAccount::KEY_USER => 'user',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_EMAIL => 'test@test.com',
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
        ];
        $commandTester = new CommandTester($this->command);
        $installerMock = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $installerMock->expects($this->once())->method('installAdminUser')->with($arguments);
        $this->installerFactoryMock->expects($this->once())->method('create')->willReturn($installerMock);
        $commandTester->execute($arguments);
        $this->assertEquals('Created admin user user' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testGetArgumentsList()
    {
        /* @var $argsList \Symfony\Component\Console\Input\InputArgument[] */
        $argsList = $this->command->getArgumentsList();
        $this->assertEquals(AdminAccount::KEY_EMAIL, $argsList[2]->getName());
    }
}
