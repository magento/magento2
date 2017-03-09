<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\BackupCommand;
use Symfony\Component\Console\Tester\CommandTester;

class BackupCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Setup\BackupRollback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollback;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Framework\Setup\BackupRollbackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollbackFactory;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    public function setUp()
    {
        $maintenanceMode = $this->getMock(\Magento\Framework\App\MaintenanceMode::class, [], [], '', false);
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->backupRollback = $this->getMock(\Magento\Framework\Setup\BackupRollback::class, [], [], '', false);
        $this->backupRollbackFactory = $this->getMock(
            \Magento\Framework\Setup\BackupRollbackFactory::class,
            [],
            [],
            '',
            false
        );
        $this->backupRollbackFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->backupRollback);
        $this->deploymentConfig = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $appState = $this->getMock(
            \Magento\Framework\App\State::class,
            [],
            [],
            '',
            false
        );
        $configLoader = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManager\ConfigLoaderInterface::class,
            [],
            '',
            false
        );
        $configLoader->expects($this->any())->method('load')->willReturn([]);

        $this->objectManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap([
                    [\Magento\Framework\Setup\BackupRollbackFactory::class, $this->backupRollbackFactory],
                    [\Magento\Framework\App\State::class, $appState],
                    [\Magento\Framework\ObjectManager\ConfigLoaderInterface::class, $configLoader],
                ])
            );
        $command = new BackupCommand(
            $objectManagerProvider,
            $maintenanceMode,
            $this->deploymentConfig
        );
        $this->tester = new CommandTester($command);
    }

    public function testExecuteCodeBackup()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--code' => true]);
    }

    public function testExecuteMediaBackup()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--media' => true]);
    }

    public function testExecuteDBBackup()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $this->backupRollback->expects($this->once())
            ->method('dbBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--db' => true]);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(false));
        $this->tester->execute(['--db' => true]);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $this->tester->getDisplay()
        );
    }

    public function testExecuteNoOptions()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(false));
        $this->tester->execute([]);
        $expected = 'Enabling maintenance mode' . PHP_EOL
            . 'Not enough information provided to take backup.' . PHP_EOL
            . 'Disabling maintenance mode' . PHP_EOL;
        $this->assertSame($expected, $this->tester->getDisplay());
    }
}
