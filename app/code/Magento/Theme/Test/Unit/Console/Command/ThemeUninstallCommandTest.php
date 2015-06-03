<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Console\Command;

use Magento\Theme\Console\Command\ThemeUninstallCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ThemeUninstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Backup\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupFS;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $file;

    /**
     * @var ThemeUninstallCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $this->backupFS = $this->getMock('Magento\Framework\Backup\Filesystem', [], [], '', false);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Backup\Filesystem', [], $this->backupFS],
            ]));
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $path = realpath(__DIR__ . '/../../_files/');
        $this->directoryList->expects($this->any())
            ->method('getRoot')
            ->willReturn($path);
        $this->directoryList->expects($this->any())
            ->method('getPath')
            ->willReturn($path);
        $this->file = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);
        $this->command = new ThemeUninstallCommand(
            $this->deploymentConfig,
            $this->maintenanceMode,
            $this->objectManager,
            $this->directoryList,
            $this->file
        );
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteWithoutApplicationInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->tester->execute(['theme' => 'test']);
        $this->assertContains(
            'You cannot run this command because the Magento application is not installed.',
            $this->tester->getDisplay()
        );
    }
    public function testExecuteWithBackupCode()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->backupFS->expects($this->once())
            ->method('addIgnorePaths');
        $this->backupFS->expects($this->once())
            ->method('setBackupsDir');
        $this->backupFS->expects($this->once())
            ->method('setBackupExtension');
        $this->backupFS->expects($this->once())
            ->method('setTime');
        $this->backupFS->expects($this->once())
            ->method('create');
        $this->backupFS->expects($this->once())
            ->method('getBackupFilename')
            ->willReturn('RollbackFile_A.tgz');
        $this->backupFS->expects($this->once())
            ->method('getBackupPath')
            ->willReturn('pathToFile/RollbackFile_A.tgz');
        $this->file->expects($this->once())->method('isExists')->willReturn(false);
        $this->file->expects($this->once())->method('createDirectory');
        $this->tester->execute(['theme' => 'test', '--backup-code' => true]);
        $this->tester->getDisplay();
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->tester->execute(['theme' => 'test']);
        $this->assertContains(
            'Enabling maintenance mode'.PHP_EOL.'Disabling maintenance mode'.PHP_EOL,
            $this->tester->getDisplay()
        );
    }
}
