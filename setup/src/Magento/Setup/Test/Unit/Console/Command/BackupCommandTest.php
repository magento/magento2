<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Console\Command\BackupCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BackupCommandTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var BackupRollback|MockObject
     */
    private $backupRollback;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var BackupRollbackFactory|MockObject
     */
    private $backupRollbackFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    protected function setUp(): void
    {
        $maintenanceMode = $this->createMock(MaintenanceMode::class);
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->backupRollback = $this->createMock(BackupRollback::class);
        $this->backupRollbackFactory = $this->createMock(BackupRollbackFactory::class);
        $this->backupRollbackFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->backupRollback);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $appState = $this->createMock(State::class);
        $configLoader = $this->getMockForAbstractClass(
            ConfigLoaderInterface::class,
            [],
            '',
            false
        );
        $configLoader->expects($this->any())->method('load')->willReturn([]);

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [BackupRollbackFactory::class, $this->backupRollbackFactory],
                    [State::class, $appState],
                    [ConfigLoaderInterface::class, $configLoader],
                ]
            );
        $command = new BackupCommand(
            $objectManagerProvider,
            $maintenanceMode,
            $this->deploymentConfig,
            new MaintenanceModeEnabler($maintenanceMode)
        );
        $this->tester = new CommandTester($command);
    }

    public function testExecuteCodeBackup()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--code' => true]);
    }

    public function testExecuteMediaBackup()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--media' => true]);
    }

    public function testExecuteDBBackup()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->backupRollback->expects($this->once())
            ->method('dbBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--db' => true]);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
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
            ->willReturn(false);
        $this->tester->execute([]);
        $expected = 'Enabling maintenance mode' . PHP_EOL
            . 'Not enough information provided to take backup.' . PHP_EOL
            . 'Disabling maintenance mode' . PHP_EOL;
        $this->assertSame($expected, $this->tester->getDisplay());
    }
}
