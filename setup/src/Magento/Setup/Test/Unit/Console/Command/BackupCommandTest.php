<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $file;

    /**
     * @var \Magento\Framework\Backup\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Backup\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    private $database;

    /**
     * @var string
     */
    private $path;

    public function setUp()
    {
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->path = realpath(__DIR__);
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->directoryList->expects($this->any())
            ->method('getRoot')
            ->willReturn($this->path);
        $this->directoryList->expects($this->any())
            ->method('getPath')
            ->willReturn($this->path);
        $configLoader = $this->getMock('Magento\Framework\App\ObjectManager\ConfigLoader', [], [], '', false);
        $configLoader->expects($this->any())
            ->method('load')
            ->willReturn([]);
        $this->file = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);
        $this->database = $this->getMock('Magento\Framework\Backup\Db', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Backup\Filesystem', [], [], '', false);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\App\State', $this->getMock('Magento\Framework\App\State', [], [], '', false)],
                ['Magento\Framework\App\ObjectManager\ConfigLoader', $configLoader],
            ]));
        $helper = $this->getMock('Magento\Framework\Backup\Filesystem\Helper', [], [], '', false);
        $helper->expects($this->any())
            ->method('getInfo')
            ->willReturn(['writable' => true]);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Backup\Filesystem\Helper', [], $helper],
                ['Magento\Framework\Backup\Filesystem', [], $this->filesystem],
                ['Magento\Framework\Backup\Db', [], $this->database],
            ]));
        $command = new BackupCommand(
            $objectManagerProvider,
            $maintenanceMode,
            $this->directoryList,
            $this->file
        );
        $this->tester = new CommandTester($command);
    }

    public function testExecuteCodeBackup()
    {
        $this->setupCodeBackupRollback();
        $this->filesystem->expects($this->once())
            ->method('create');
        $this->file->expects($this->once())->method('isExists')->with($this->path . '/backups')->willReturn(false);
        $this->file->expects($this->once())->method('createDirectory')->with($this->path . '/backups', 0777);
        $this->tester->execute(['--code' => true]);
        $expectedMsg = 'Enabling maintenance mode' . PHP_EOL
            . 'Code backup is started ...' . PHP_EOL
            . 'Code backup filename: RollbackFile_A.tgz (The archive can be uncompressed with 7-Zip on Windows systems)'
            . PHP_EOL . 'Code backup path: pathToFile/RollbackFile_A.tgz' . PHP_EOL
            . '[SUCCESS]: Code backup is completed successfully.' . PHP_EOL
            . 'Disabling maintenance mode' . PHP_EOL;
        $this->assertEquals($expectedMsg, $this->tester->getDisplay());
    }

    public function testExecuteDataBackup()
    {
        $this->setupDataBackupRollback();
        $this->database->expects($this->once())
            ->method('create');
        $this->file->expects($this->exactly(2))->method('isExists')->with($this->path . '/backups')->willReturn(false);
        $this->file->expects($this->exactly(2))->method('createDirectory')->with($this->path . '/backups', 0777);
        $this->tester->execute(['--data' => true]);
        $expectedMsg = 'Enabling maintenance mode' . PHP_EOL
            . 'DB backup is started ...' . PHP_EOL
            . 'DB backup filename: RollbackFile_A.tgz (The archive can be uncompressed with 7-Zip on Windows systems)'
            . PHP_EOL . 'DB backup path: pathToFile/RollbackFile_A.tgz' . PHP_EOL
            . '[SUCCESS]: DB backup is completed successfully.' . PHP_EOL
            . 'Media backup is started ...' . PHP_EOL
            . 'Media backup filename:  (The archive can be uncompressed with 7-Zip on Windows systems)' . PHP_EOL
            . 'Media backup path: ' . PHP_EOL
            . '[SUCCESS]: Media backup is completed successfully.' . PHP_EOL
            . 'Disabling maintenance mode' . PHP_EOL;
        $this->assertEquals($expectedMsg, $this->tester->getDisplay());
    }

    private function setupCodeBackupRollback()
    {
        $this->filesystem->expects($this->once())
            ->method('addIgnorePaths');
        $this->filesystem->expects($this->once())
            ->method('setBackupsDir');
        $this->filesystem->expects($this->once())
            ->method('setBackupExtension');
        $this->filesystem->expects($this->once())
            ->method('setTime');
        $this->filesystem->expects($this->once())
            ->method('getBackupFilename')
            ->willReturn('RollbackFile_A.tgz');
        $this->filesystem->expects($this->once())
            ->method('getBackupPath')
            ->willReturn('pathToFile/RollbackFile_A.tgz');
    }

    private function setupDataBackupRollback()
    {
        $this->database->expects($this->once())
            ->method('setBackupsDir');
        $this->database->expects($this->once())
            ->method('setBackupExtension');
        $this->database->expects($this->once())
            ->method('setTime');
        $this->database->expects($this->once())
            ->method('getBackupFilename')
            ->willReturn('RollbackFile_A.tgz');
        $this->database->expects($this->once())
            ->method('getBackupPath')
            ->willReturn('pathToFile/RollbackFile_A.tgz');
    }
}
