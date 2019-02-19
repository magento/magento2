<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Backup\Factory;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupRollbackTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\Backup\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Backup\Filesystem\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Magento\Framework\Backup\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    private $database;

    /**
     * @var string
     */
    private $path;

    protected function setUp()
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->log = $this->createMock(\Magento\Framework\Setup\LoggerInterface::class);
        $this->directoryList = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $this->path = realpath(__DIR__);
        $this->directoryList->expects($this->any())
            ->method('getRoot')
            ->willReturn($this->path);
        $this->directoryList->expects($this->any())
            ->method('getPath')
            ->willReturn($this->path);
        $this->file = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);
        $this->filesystem = $this->createMock(\Magento\Framework\Backup\Filesystem::class);
        $this->database = $this->createMock(\Magento\Framework\Backup\Db::class);
        $this->helper = $this->createMock(\Magento\Framework\Backup\Filesystem\Helper::class);
        $this->helper->expects($this->any())
            ->method('getInfo')
            ->willReturn(['writable' => true, 'size' => 100]);
        $configLoader = $this->createMock(\Magento\Framework\App\ObjectManager\ConfigLoader::class);
        $configLoader->expects($this->any())
            ->method('load')
            ->willReturn([]);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                [
                    \Magento\Framework\App\State::class, $this->createMock(\Magento\Framework\App\State::class)
                ],
                [\Magento\Framework\ObjectManager\ConfigLoaderInterface::class, $configLoader],
            ]));
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                [\Magento\Framework\Backup\Filesystem\Helper::class, [], $this->helper],
                [\Magento\Framework\Backup\Filesystem::class, [], $this->filesystem],
                [\Magento\Framework\Backup\Db::class, [], $this->database],
            ]));
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

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage This backup type \'txt\' is not supported.
     */
    public function testCodeBackupWithInvalidType()
    {
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

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The rollback file doesn't exist. Verify the file and try again.
     */
    public function testCodeRollbackWithInvalidFilePath()
    {
        $this->file->expects($this->once())
            ->method('isExists')
            ->willReturn(false);
        $this->model->codeRollback('12345_filesystem_code.tgz');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The rollback file is invalid. Verify the file and try again.
     */
    public function testCodeRollbackWithInvalidFileType()
    {
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
