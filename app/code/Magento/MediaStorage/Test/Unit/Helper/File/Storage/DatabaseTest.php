<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Helper\File\Storage;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage;
use Magento\MediaStorage\Model\File\Storage\DatabaseFactory;
use Magento\MediaStorage\Model\File\Storage\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DatabaseTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /** @var DatabaseFactory|MockObject  */
    protected $dbStorageFactoryMock;

    /** @var Filesystem|MockObject  */
    protected $filesystemMock;

    /** @var File|MockObject  */
    protected $fileStorageMock;

    /** @var ScopeConfigInterface|MockObject  */
    protected $configMock;

    /** @var Database */
    protected $helper;

    protected function setUp(): void
    {
        $this->dbStorageFactoryMock = $this->getMockBuilder(
            DatabaseFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->objectManager = new ObjectManager($this);
        $className = Database::class;
        $arguments = $this->objectManager->getConstructArguments(
            $className,
            ['dbStorageFactory' => $this->dbStorageFactoryMock]
        );
        /** @var Context $context */
        $context = $arguments['context'];
        $mediaDirMock = $this->getMockForAbstractClass(ReadInterface::class);
        $mediaDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('media-dir');
        $this->filesystemMock = $arguments['filesystem'];
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirMock);
        $this->fileStorageMock = $arguments['fileStorage'];
        $this->configMock = $context->getScopeConfig();
        $this->helper = $this->objectManager->getObject($className, $arguments);
    }

    /**
     * @param int $storage
     * @param bool $expected
     * @dataProvider checkDbUsageDataProvider
     */
    public function testCheckDbUsage($storage, $expected)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);

        $this->assertEquals($expected, $this->helper->checkDbUsage());
        $this->assertEquals($expected, $this->helper->checkDbUsage());
    }

    /**
     * @return array
     */
    public function checkDbUsageDataProvider()
    {
        return [
            'media database' => [Storage::STORAGE_MEDIA_DATABASE, true],
            'non-media database' => [10, false],
        ];
    }

    public function testGetStorageDatabaseModel()
    {
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($dbModelMock);
        $this->assertSame($dbModelMock, $this->helper->getStorageDatabaseModel());
        $this->assertSame($dbModelMock, $this->helper->getStorageDatabaseModel());
    }

    public function testGetStorageFileModel()
    {
        $this->assertSame($this->fileStorageMock, $this->helper->getStorageFileModel());
    }

    public function testGetResourceStorageModel()
    {
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($dbModelMock);
        $resourceModelMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMockForAbstractClass();
        $dbModelMock->expects($this->once())
            ->method('getResource')
            ->willReturn($resourceModelMock);

        $this->assertSame($resourceModelMock, $this->helper->getResourceStorageModel());
        $this->assertSame($resourceModelMock, $this->helper->getResourceStorageModel());
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @dataProvider updateFileDataProvider
     */
    public function testSaveFile($storage, $callNum)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $dbModelMock->expects($this->exactly($callNum))
            ->method('saveFile')
            ->with('filename');

        $this->helper->saveFile('media-dir/filename');
    }

    /**
     * @return array
     */
    public function updateFileDataProvider()
    {
        return [
            'media database' => [Storage::STORAGE_MEDIA_DATABASE, 1],
            'non-media database' => [10, 0],
        ];
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @dataProvider updateFileDataProvider
     */
    public function testRenameFile($storage, $callNum)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $dbModelMock->expects($this->exactly($callNum))
            ->method('renameFile')
            ->with('oldName', 'newName');

        $this->helper->renameFile('media-dir/oldName', 'media-dir/newName');
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @dataProvider updateFileDataProvider
     */
    public function testCopyFile($storage, $callNum)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $dbModelMock->expects($this->exactly($callNum))
            ->method('copyFile')
            ->with('oldName', 'newName');

        $this->helper->copyFile('media-dir/oldName', 'media-dir/newName');
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @param bool|null $expected
     * @dataProvider fileExistsDataProvider
     */
    public function testFileExists($storage, $callNum, $expected)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $dbModelMock->expects($this->exactly($callNum))
            ->method('fileExists')
            ->with('file')
            ->willReturn(true);

        $this->assertEquals($expected, $this->helper->fileExists('media-dir/file'));
    }

    /**
     * @return array
     */
    public function fileExistsDataProvider()
    {
        return [
            'media database' => [Storage::STORAGE_MEDIA_DATABASE, 1, true],
            'non-media database' => [10, 0, null],
        ];
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @param string $expected
     * @dataProvider getUniqueFilenameDataProvider
     */
    public function testGetUniqueFilename($storage, $callNum, $expected)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $map = [
            ['directory/filename.ext', true],
            ['directory/filename_1.ext', true],
            ['directory/filename_2.ext', false],
        ];
        $dbModelMock->expects($this->any())
            ->method('fileExists')
            ->willReturnMap($map);

        $this->assertSame($expected, $this->helper->getUniqueFilename('media-dir/directory/', 'filename.ext'));
    }

    /**
     * @return array
     */
    public function getUniqueFilenameDataProvider()
    {
        return [
            'media database' => [Storage::STORAGE_MEDIA_DATABASE, 1, 'filename_2.ext'],
            'non-media database' => [10, 0, 'filename.ext'],
        ];
    }

    /**
     * @param bool $expected
     * @param int $storage
     * @param int $callNum
     * @param int $id
     * @param int $callSaveFile
     * @dataProvider saveFileToFileSystemDataProvider
     */
    public function testSaveFileToFileSystem($expected, $storage, $callNum, $id = 0, $callSaveFile = 0)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $dbModelMock->expects($this->exactly($callNum))
            ->method('loadByFilename')
            ->with('filename')->willReturnSelf();
        $dbModelMock->expects($this->exactly($callNum))
            ->method('getId')
            ->willReturn($id);
        $dbModelMock->expects($this->exactly($callSaveFile))
            ->method('getData')
            ->willReturn(['data']);
        $this->fileStorageMock->expects($this->exactly($callSaveFile))
            ->method('saveFile')
            ->willReturn(true);
        $this->assertEquals($expected, $this->helper->saveFileToFilesystem('media-dir/filename'));
    }

    /**
     * @return array
     */
    public function saveFileToFileSystemDataProvider()
    {
        return [
            'media database, no id' => [
                false,
                Storage::STORAGE_MEDIA_DATABASE,
                1,
            ],
            'media database, with id' => [
                true,
                Storage::STORAGE_MEDIA_DATABASE,
                1,
                1,
                1,
            ],
            'non-media database' => [false, 10, 0],
        ];
    }

    public function testGetMediaRelativePath()
    {
        $this->assertEquals('fullPath', $this->helper->getMediaRelativePath('media-dir/fullPath'));
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @dataProvider updateFileDataProvider
     */
    public function testDeleteFolder($storage, $callNum)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $resourceModelMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteFolder', '__wakeup'])
            ->getMockForAbstractClass();
        $dbModelMock->expects($this->exactly($callNum))
            ->method('getResource')
            ->willReturn($resourceModelMock);
        $resourceModelMock->expects($this->exactly($callNum))
            ->method('deleteFolder')
            ->with('folder');

        $this->helper->deleteFolder('media-dir/folder');
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @dataProvider updateFileDataProvider
     */
    public function testDeleteFile($storage, $callNum)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $dbModelMock->expects($this->exactly($callNum))
            ->method('deleteFile')
            ->with('file');

        $this->helper->deleteFile('media-dir/file');
    }

    /**
     * @param array $result
     * @param string $expected
     * @param int $storage
     * @param int $callNum
     * @param int $callDirWrite
     * @dataProvider saveUploadedFileDataProvider
     */
    public function testSaveUploadedFile($result, $expected, $expectedFullPath, $storage, $callNum, $callDirWrite = 0)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);
        $dbModelMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->willReturn($dbModelMock);
        $dirWriteMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filesystemMock->expects($this->exactly($callDirWrite))
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($dirWriteMock);
        $dirWriteMock->expects($this->exactly($callDirWrite))
            ->method('renameFile');
        $map = [
            ['directory/filename.ext', true],
            ['directory/filename_1.ext', true],
            ['directory/filename_2.ext', false],
        ];
        $dbModelMock->expects($this->any())
            ->method('fileExists')
            ->willReturnMap($map);
        $dbModelMock->expects($this->exactly($callNum))
            ->method('saveFile')
            ->with($expectedFullPath);
        $this->assertEquals($expected, $this->helper->saveUploadedFile($result));
    }

    /**
     * @return array
     */
    public function saveUploadedFileDataProvider()
    {
        return [
            'media database, file not unique' => [
                ['file' => 'filename.ext', 'path' => 'media-dir/directory/'],
                '/filename_2.ext',
                'directory/filename_2.ext',
                Storage::STORAGE_MEDIA_DATABASE,
                1,
                1,
            ],
            'media database, file unique' => [
                ['file' => 'file.ext', 'path' => 'media-dir/directory/'],
                '/file.ext',
                'directory/file.ext',
                Storage::STORAGE_MEDIA_DATABASE,
                1,
            ],
            'non-media database' => [
                ['file' => 'filename.ext', 'path' => 'media-dir/directory/'],
                'filename.ext',
                '',
                10,
                0,
            ],
        ];
    }

    public function testGetMediaBaseDir()
    {
        $mediaDirMock = $this->getMockForAbstractClass(ReadInterface::class);
        $mediaDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('media-dir');
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirMock);

        $this->helper = $this->objectManager->getObject(
            Database::class,
            [
                'filesystem' => $filesystemMock,
                'fileStorage' => $this->fileStorageMock,
                'dbStorageFactory' => $this->dbStorageFactoryMock,
                'config' => $this->configMock,
            ]
        );

        $this->assertEquals('media-dir', $this->helper->getMediaBaseDir());
        $this->assertEquals('media-dir', $this->helper->getMediaBaseDir());
    }
}
