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
    public function testExecute()
    {
        $arguments = [
            AdminAccount::KEY_USER => 'user',
            AdminAccount::KEY_EMAIL => 'test@test.com',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
        ];

        $installerMock = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $installerMock->expects($this->once())->method('installAdminUser')->with($arguments);
        $installerFactoryMock = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $installerFactoryMock->expects($this->once())->method('create')->willReturn($installerMock);

        $command = new AdminUserCreateCommand($installerFactoryMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments);

        $this->assertEquals('Created admin user user' . PHP_EOL, $commandTester->getDisplay());
    }
}
