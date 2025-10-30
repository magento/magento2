<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\State;
use Magento\Framework\Backup\Db;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Backup\Filesystem;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\ConsoleLoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupRollbackTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var ConsoleLoggerInterface|MockObject
     */
    private $log;

    /**
     * @var DirectoryList|MockObject
     */
    private $directoryList;

    /**
     * @var BackupRollback
     */
    private $model;

    /**
     * @var File|MockObject
     */
    private $file;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var Helper|MockObject
     */
    private $helper;

    /**
     * @var Db|MockObject
     */
    private $database;

    /**
     * @var string
     */
    private $path;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->log = $this->getMockForAbstractClass(ConsoleLoggerInterface::class);
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->path = realpath(__DIR__);
        $this->directoryList->expects($this->any())
            ->method('getRoot')
            ->willReturn($this->path);
        $this->directoryList->expects($this->any())
            ->method('getPath')
            ->willReturn($this->path);
        $this->file = $this->createMock(File::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->database = $this->createMock(Db::class);
        $this->helper = $this->createMock(Helper::class);
        $this->helper->expects($this->any())
            ->method('getInfo')
            ->willReturn(['writable' => true, 'size' => 100]);
        $configLoader = $this->createMock(ConfigLoader::class);
        $configLoader->expects($this->any())
            ->method('load')
            ->willReturn([]);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [
                    State::class, $this->createMock(State::class)
                ],
                [ConfigLoaderInterface::class, $configLoader],
            ]);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [Helper::class, [], $this->helper],
                [Filesystem::class, [], $this->filesystem],
                [Db::class, [], $this->database],
            ]);
        $this->model = new BackupRollback(
            $this->objectManager,
            $this->log,
            $this->directoryList,
            $this->file,
            $this->helper
        );
    }

    public function testCodeBackup()
    {
        $this->setupCodeBackupRollback();
        $this->filesystem->expects($this->once())
            ->method('create');
        $this->file->expects($this->once())->method('isExists')->with($this->path . '/backups')->willReturn(false);
        $this->file->expects($this->once())->method('createDirectory')->with($this->path . '/backups', 0777);
        $this->model->codeBackup(time());
    }

    public function testCodeBackupWithInvalidType()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('This backup type \\\'txt\\\' is not supported.');
        $this->model->codeBackup(time(), 'txt');
    }

    public function testCodeRollback()
    {
        $this->filesystem->expects($this->once())->method('rollback');
        $this->setupCodeBackupRollback();
        $this->file->expects($this->once())
            ->method('isExists')
            ->with($this->path . '/backups/12345_filesystem_code.tgz')
            ->willReturn(true);
        $this->model->codeRollback('12345_filesystem_code.tgz');
    }

    public function testCodeRollbackWithInvalidFilePath()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The rollback file doesn\'t exist. Verify the file and try again.');
        $this->file->expects($this->once())
            ->method('isExists')
            ->willReturn(false);
        $this->model->codeRollback('12345_filesystem_code.tgz');
    }

    public function testCodeRollbackWithInvalidFileType()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The rollback file is invalid. Verify the file and try again.');
        $this->model->codeRollback('RollbackFile_A.txt');
    }

    public function testMediaBackup()
    {
        $this->setupCodeBackupRollback();
        $this->filesystem->expects($this->once())
            ->method('create');
        $this->file->expects($this->once())->method('isExists')->with($this->path . '/backups')->willReturn(false);
        $this->file->expects($this->once())->method('createDirectory')->with($this->path . '/backups', 0777);
        $this->model->codeBackup(time(), Factory::TYPE_MEDIA);
    }

    public function testMediaRollback()
    {
        $this->filesystem->expects($this->once())->method('rollback');
        $this->setupCodeBackupRollback();
        $this->file->expects($this->once())
            ->method('isExists')
            ->with($this->path . '/backups/12345_filesystem_media.tgz')
            ->willReturn(true);
        $this->model->codeRollback('12345_filesystem_media.tgz', Factory::TYPE_MEDIA);
    }

    public function testDbBackup()
    {
        $this->setupDbBackupRollback();
        $this->database->expects($this->once())->method('getBackupFilename')->willReturn('RollbackFile_A.gz');
        $this->database->expects($this->once())->method('create');
        $this->file->expects($this->once())->method('isExists')->willReturn(false);
        $this->file->expects($this->once())->method('createDirectory');
        $this->model->dbBackup(time());
    }

    public function testDbRollback()
    {
        $this->setupDbBackupRollback();

        $this->database->expects($this->once())->method('rollback');
        $this->database->expects($this->exactly(2))->method('getBackupFilename')
            ->willReturnOnConsecutiveCalls('test', '1510140748_db_test_backup');
        $this->database->expects($this->once())->method('getTime')->willReturn(1510140748);
        $this->database->expects($this->once())->method('getType')->willReturn('db');
        $this->database->expects($this->once())->method('setName')->with(' test backup');

        $this->file->expects($this->once())
            ->method('isExists')
            ->with($this->path . '/backups/1510140748_db_test_backup.sql')
            ->willReturn(true);

        $this->model->dbRollback('1510140748_db_test_backup.sql');
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
        $this->filesystem->expects($this->atLeastOnce())
            ->method('getBackupPath')
            ->willReturn('pathToFile/12345_filesystem_code.tgz');
        $this->log->expects($this->once())
            ->method('logSuccess');
    }

    private function setupDbBackupRollback()
    {
        $this->database->expects($this->once())
            ->method('setBackupsDir');
        $this->database->expects($this->once())
            ->method('setBackupExtension');
        $this->database->expects($this->once())
            ->method('setTime');
        $this->database->expects($this->atLeastOnce())
            ->method('getBackupPath')
            ->willReturn('pathToFile/12345_db.sql');
        $this->log->expects($this->once())
            ->method('logSuccess');
    }

    public function testGetFSDiskSpaceback()
    {
        $size = $this->model->getFSDiskSpace();
        $this->assertEquals(100, $size);
    }

    public function testGetDBDiskSpace()
    {
        $this->database->expects($this->once())->method('getDBSize')->willReturn(100);
        $size = $this->model->getDBDiskSpace();
        $this->assertEquals(100, $size);
    }
}
