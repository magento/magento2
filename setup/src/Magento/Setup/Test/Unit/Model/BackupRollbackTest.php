<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\BackupRollback;
use Magento\Setup\Model\LoggerInterface;

class BackupRollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $log;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var BackupRollback
     */
    private $model;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $file;

    /**
     * @var string
     */
    private $path;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $this->log = $this->getMock('Magento\Setup\Model\LoggerInterface', [], [], '', false);
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->path = realpath(__DIR__);
        $this->directoryList->expects($this->any())
            ->method('getRoot')
            ->willReturn($this->path);
        $this->directoryList->expects($this->any())
            ->method('getPath')
            ->willReturn($this->path);
        $this->file = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);
        $this->model = new BackupRollback(
            $this->objectManager,
            $this->log,
            $this->directoryList,
            $this->file
        );
    }

    public function testCodeBackup()
    {
        $filesystem = $this->getMock('Magento\Framework\Backup\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('addIgnorePaths');
        $filesystem->expects($this->once())
            ->method('setBackupsDir');
        $filesystem->expects($this->once())
            ->method('setBackupExtension');
        $filesystem->expects($this->once())
            ->method('setTime');
        $filesystem->expects($this->once())
            ->method('create');
        $filesystem->expects($this->once())
            ->method('getBackupFilename')
            ->willReturn('RollbackFile_A.tgz');
        $filesystem->expects($this->once())
            ->method('getBackupPath')
            ->willReturn('pathToFile/RollbackFile_A.tgz');
        $this->log->expects($this->once())
            ->method('logSuccess');
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Backup\Filesystem', [], $filesystem],
            ]));
        $this->file->expects($this->once())->method('isExists')->with($this->path . '/backups')->willReturn(false);
        $this->file->expects($this->once())->method('createDirectory')->with($this->path . '/backups', 0777);
        $this->model->codeBackup();
    }

    public function testCodeRollback()
    {
        $helper = $this->getMock('Magento\Framework\Backup\Filesystem\Helper', [], [], '', false);
        $helper->expects($this->once())
            ->method('getInfo')
            ->willReturn(['writable' => true]);
        $filesystem = $this->getMock('Magento\Framework\Backup\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('addIgnorePaths');
        $filesystem->expects($this->once())
            ->method('setBackupsDir');
        $filesystem->expects($this->once())
            ->method('setBackupExtension');
        $filesystem->expects($this->once())
            ->method('setTime');
        $filesystem->expects($this->once())
            ->method('rollback');
        $filesystem->expects($this->once())
            ->method('getBackupFilename')
            ->willReturn('RollbackFile_A.tgz');
        $filesystem->expects($this->once())
            ->method('getBackupPath')
            ->willReturn('pathToFile/RollbackFile_A.tgz');
        $this->log->expects($this->once())
            ->method('logSuccess');
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Backup\Filesystem\Helper', [], $helper],
                ['Magento\Framework\Backup\Filesystem', [], $filesystem],
            ]));
        $this->file->expects($this->once())
            ->method('isExists')
            ->with($this->path . '/backups/RollbackFile_A.tgz')
            ->willReturn(true);
        $this->model->codeRollback('RollbackFile_A.tgz');
    }
}
