<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Helper\File\Storage;

use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\App\Filesystem\DirectoryList;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory | \PHPUnit_Framework_MockObject_MockObject  */
    protected $dbStorageFactoryMock;

    /** @var \Magento\Framework\Filesystem | \PHPUnit_Framework_MockObject_MockObject  */
    protected $filesystemMock;

    /** @var \Magento\MediaStorage\Model\File\Storage\File | \PHPUnit_Framework_MockObject_MockObject  */
    protected $fileStorageMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $configMock;

    /** @var Database */
    protected $helper;

    public function setUp()
    {
        $this->dbStorageFactoryMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\DatabaseFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Magento\MediaStorage\Helper\File\Storage\Database';
        $arguments = $this->objectManager->getConstructArguments(
            $className,
            ['dbStorageFactory' => $this->dbStorageFactoryMock]
        );
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $mediaDirMock = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $mediaDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('media-dir'));
        $this->filesystemMock = $arguments['filesystem'];
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($mediaDirMock));
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));

        $this->assertEquals($expected, $this->helper->checkDbUsage());
        $this->assertEquals($expected, $this->helper->checkDbUsage());
    }

    public function checkDbUsageDataProvider()
    {
        return [
            'media database' => [\Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE, true],
            'non-media database' => [10, false],
        ];
    }

    public function testGetStorageDatabaseModel()
    {
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->assertSame($dbModelMock, $this->helper->getStorageDatabaseModel());
        $this->assertSame($dbModelMock, $this->helper->getStorageDatabaseModel());
    }

    public function testGetStorageFileModel()
    {
        $this->assertSame($this->fileStorageMock, $this->helper->getStorageFileModel());
    }

    public function testGetResourceStorageModel()
    {
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $resourceModelMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMockForAbstractClass();
        $dbModelMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($resourceModelMock));

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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('saveFile')
            ->with('filename');

        $this->helper->saveFile('media-dir/filename');
    }

    public function updateFileDataProvider()
    {
        return [
            'media database' => [\Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE, 1],
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('fileExists')
            ->with('file')
            ->will($this->returnValue(true));

        $this->assertEquals($expected, $this->helper->fileExists('media-dir/file'));
    }

    public function fileExistsDataProvider()
    {
        return [
            'media database' => [\Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE, 1, true],
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $map = [
            ['directory/filename.ext', true],
            ['directory/filename_1.ext', true],
            ['directory/filename_2.ext', false],
        ];
        $dbModelMock->expects($this->any())
            ->method('fileExists')
            ->will($this->returnValueMap($map));

        $this->assertSame($expected, $this->helper->getUniqueFilename('media-dir/directory/', 'filename.ext'));
    }

    public function getUniqueFilenameDataProvider()
    {
        return [
            'media database' => [\Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE, 1, 'filename_2.ext'],
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('loadByFilename')
            ->with('filename')
            ->will($this->returnSelf());
        $dbModelMock->expects($this->exactly($callNum))
            ->method('getId')
            ->will($this->returnValue($id));
        $dbModelMock->expects($this->exactly($callSaveFile))
            ->method('getData')
            ->will($this->returnValue(['data']));
        $this->fileStorageMock->expects($this->exactly($callSaveFile))
            ->method('saveFile')
            ->will($this->returnValue(true));
        $this->assertEquals($expected, $this->helper->saveFileToFilesystem('media-dir/filename'));
    }

    public function saveFileToFileSystemDataProvider()
    {
        return [
            'media database, no id' => [
                false,
                \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE,
                1,
            ],
            'media database, with id' => [
                true,
                \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE,
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $resourceModelMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['deleteFolder', '__wakeup'])
            ->getMockForAbstractClass();
        $dbModelMock->expects($this->exactly($callNum))
            ->method('getResource')
            ->will($this->returnValue($resourceModelMock));
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
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
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $dirWriteMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock->expects($this->exactly($callDirWrite))
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue($dirWriteMock));
        $dirWriteMock->expects($this->exactly($callDirWrite))
            ->method('renameFile');
        $map = [
            ['directory/filename.ext', true],
            ['directory/filename_1.ext', true],
            ['directory/filename_2.ext', false],
        ];
        $dbModelMock->expects($this->any())
            ->method('fileExists')
            ->will($this->returnValueMap($map));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('saveFile')
            ->with($expectedFullPath);
        $this->assertEquals($expected, $this->helper->saveUploadedFile($result));
    }

    public function saveUploadedFileDataProvider()
    {
        return [
            'media database, file not unique' => [
                ['file' => 'filename.ext', 'path' => 'media-dir/directory/'],
                '/filename_2.ext',
                'directory/filename_2.ext',
                \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE,
                1,
                1,
            ],
            'media database, file unique' => [
                ['file' => 'file.ext', 'path' => 'media-dir/directory/'],
                '/file.ext',
                'directory/file.ext',
                \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE,
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
        $mediaDirMock = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $mediaDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('media-dir'));
        $filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($mediaDirMock));

        $this->helper = $this->objectManager->getObject(
            'Magento\MediaStorage\Helper\File\Storage\Database',
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
