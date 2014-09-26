<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Helper\File\Storage;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \Magento\Core\Model\File\Storage\DatabaseFactory | \PHPUnit_Framework_MockObject_MockObject  */
    protected $dbStorageFactoryMock;

    /** @var \Magento\Framework\App\Filesystem | \PHPUnit_Framework_MockObject_MockObject  */
    protected $filesystemMock;

    /** @var \Magento\Core\Model\File\Storage\File | \PHPUnit_Framework_MockObject_MockObject  */
    protected $fileStorageMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $configMock;

    /** @var Database */
    protected $helper;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->dbStorageFactoryMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\DatabaseFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\App\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileStorageMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\File')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $this->objectManager->getObject(
            'Magento\Core\Helper\File\Storage\Database',
            [
                'filesystem' => $this->filesystemMock,
                'fileStorage' => $this->fileStorageMock,
                'dbStorageFactory' => $this->dbStorageFactoryMock,
                'config' => $this->configMock,
            ]
        );
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));

        $this->assertEquals($expected, $this->helper->checkDbUsage());
        $this->assertEquals($expected, $this->helper->checkDbUsage());
    }

    public function checkDbUsageDataProvider()
    {
        return [
            'media database' => [\Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE, true],
            'non-media database' => [10, false],
        ];
    }

    public function testGetStorageDatabaseModel()
    {
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
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
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $resourceModelMock = $this->getMockBuilder('Magento\Framework\Model\Resource\Db\AbstractDb')
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('saveFile');

        $this->helper->saveFile('filename');
    }

    public function updateFileDataProvider()
    {
        return [
            'media database' => [\Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE, 1],
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('renameFile');

        $this->helper->renameFile('oldName', 'newName');
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('copyFile');

        $this->helper->copyFile('oldName', 'newName');
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('fileExists')
            ->will($this->returnValue(true));

        $this->assertEquals($expected, $this->helper->fileExists('file'));
    }

    public function fileExistsDataProvider()
    {
        return [
            'media database' => [\Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE, 1, true],
            'non-media database' => [10, 0, null],
        ];
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @dataProvider getUniqueFilenameDataProvider
     */
    public function testGetUniqueFilename($storage, $callNum)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $map = [
            ['directory/filename.ext', true],
            ['directory/filename_1.ext', true],
            ['directory/filename_2.ext', false],
        ];
        $dbModelMock->expects($this->any())
            ->method('fileExists')
            ->will($this->returnValueMap($map));

        $this->helper->getUniqueFilename('directory/', 'filename.ext');
    }

    public function getUniqueFilenameDataProvider()
    {
        return [
            'media database' => [\Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE, 1, 'filename_2.ext'],
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('loadByFilename')
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
        $this->assertEquals($expected, $this->helper->saveFileToFilesystem('filename'));
    }

    public function saveFileToFileSystemDataProvider()
    {
        return [
            'media database, no id' => [
                false,
                \Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE,
                1,
            ],
            'media database, with id' => [
                true,
                \Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE,
                1,
                1,
                1,
            ],
            'non-media database' => [false, 10, 0,],
        ];
    }

    public function testGetMediaRelativePath()
    {
        $this->filesystemMock->expects($this->once())
            ->method('getPath')
            ->with(\Magento\Framework\App\Filesystem::MEDIA_DIR)
            ->will($this->returnValue('/media/dir/'));
        $this->assertEquals('fullPath', $this->helper->getMediaRelativePath('/media/dir/fullPath'));
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $resourceModelMock = $this->getMockBuilder('Magento\Framework\Model\Resource\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['deleteFolder', '__wakeup'])
            ->getMockForAbstractClass();
        $dbModelMock->expects($this->exactly($callNum))
            ->method('getResource')
            ->will($this->returnValue($resourceModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $resourceModelMock->expects($this->exactly($callNum))
            ->method('deleteFolder');

        $this->helper->deleteFolder('folder');
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $dbModelMock->expects($this->exactly($callNum))
            ->method('deleteFile');

        $this->helper->deleteFile('file');
    }

    /**
     * @param array $result
     * @param string $expected
     * @param int $storage
     * @param int $callNum
     * @param int $callDirWrite
     * @dataProvider saveUploadedFileDataProvider
     */
    public function testSaveUploadedFile($result, $expected, $storage, $callNum, $callDirWrite = 0)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));
        $dbModelMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbStorageFactoryMock->expects($this->exactly($callNum))
            ->method('create')
            ->will($this->returnValue($dbModelMock));
        $this->filesystemMock->expects($this->exactly($callNum))
            ->method('getPath')
            ->will($this->returnValue('media-dir'));
        $dirWriteMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock->expects($this->exactly($callDirWrite))
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::ROOT_DIR)
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
            ->method('saveFile');
        $this->assertEquals($expected, $this->helper->saveUploadedFile($result));
    }

    public function saveUploadedFileDataProvider()
    {
        return [
            'media database, file not unique' => [
                ['file' => 'filename.ext', 'path' => 'directory/'],
                '/filename_2.ext',
                \Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE,
                1,
                1,
            ],
            'media database, file unique' => [
                ['file' => 'file.ext', 'path' => 'directory/'],
                '/file.ext',
                \Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE,
                1,
            ],
            'non-media database' => [
                ['file' => 'filename.ext', 'path' => 'directory/'],
                'filename.ext',
                10,
                0,
            ],
        ];
    }

    public function testGetMediaBaseDir()
    {
        $this->filesystemMock->expects($this->once())
            ->method('getPath')
            ->with(\Magento\Framework\App\Filesystem::MEDIA_DIR)
            ->will($this->returnValue('/media/dir/'));

        $this->assertEquals('/media/dir', $this->helper->getMediaBaseDir());
        $this->assertEquals('/media/dir', $this->helper->getMediaBaseDir());
    }
}
