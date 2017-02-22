<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Cms\Model\Wysiwyg\Images\Storage\Collection as StorageCollection;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Directory paths samples
     */
    const STORAGE_ROOT_DIR = '/storage/root/dir';

    const INVALID_DIRECTORY_OVER_ROOT = '/storage/some/another/dir';

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    protected $imagesStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelperMock;

    /**
     * @var array()
     */
    protected $resizeParameters;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageCollectionFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageFileFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageDatabaseFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryDatabaseFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryCollectionMock;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uploaderFactoryMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendUrlMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $driverMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorageMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerHelper;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->driverMock = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\DriverInterface',
            [],
            '',
            false,
            false,
            true,
            ['getRealPath']
        );
        $this->driverMock->expects($this->any())->method('getRealPath')->will($this->returnArgument(0));

        $this->directoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            ['delete', 'getDriver', 'create'],
            [],
            '',
            false
        );
        $this->directoryMock->expects(
            $this->any()
        )->method(
            'getDriver'
        )->will(
            $this->returnValue($this->driverMock)
        );

        $this->filesystemMock = $this->getMock(
            'Magento\Framework\Filesystem',
            ['getDirectoryWrite'],
            [],
            '',
            false
        );
        $this->filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::MEDIA
        )->will(
            $this->returnValue($this->directoryMock)
        );

        $this->adapterFactoryMock = $this->getMock(
            'Magento\Framework\Image\AdapterFactory',
            [],
            [],
            '',
            false
        );
        $this->imageHelperMock = $this->getMock(
            'Magento\Cms\Helper\Wysiwyg\Images',
            ['getStorageRoot'],
            [],
            '',
            false
        );
        $this->imageHelperMock->expects(
            $this->any()
        )->method(
            'getStorageRoot'
        )->will(
            $this->returnValue(self::STORAGE_ROOT_DIR)
        );

        $this->resizeParameters = ['width' => 100, 'height' => 50];

        $this->storageCollectionFactoryMock = $this->getMock(
            'Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->storageFileFactoryMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\FileFactory',
            [],
            [],
            '',
            false
        );
        $this->storageDatabaseFactoryMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\DatabaseFactory',
            [],
            [],
            '',
            false
        );
        $this->directoryDatabaseFactoryMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->directoryCollectionMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\Directory\Database',
            [],
            [],
            '',
            false
        );

        $this->uploaderFactoryMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\UploaderFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMock('Magento\Backend\Model\Session', [], [], '', false);
        $this->backendUrlMock = $this->getMock('Magento\Backend\Model\Url', [], [], '', false);

        $this->coreFileStorageMock = $this->getMockBuilder('Magento\MediaStorage\Helper\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->imagesStorage = $this->objectManagerHelper->getObject(
            'Magento\Cms\Model\Wysiwyg\Images\Storage',
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false),
                'storageCollectionFactory' => $this->storageCollectionFactoryMock,
                'storageFileFactory' => $this->storageFileFactoryMock,
                'storageDatabaseFactory' => $this->storageDatabaseFactoryMock,
                'directoryDatabaseFactory' => $this->directoryDatabaseFactoryMock,
                'uploaderFactory' => $this->uploaderFactoryMock,
                'resizeParameters' => $this->resizeParameters,
                'dirs' => [
                    'exclude' => [],
                    'include' => []
                ]
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::getResizeWidth
     */
    public function testGetResizeWidth()
    {
        $this->assertEquals(100, $this->imagesStorage->getResizeWidth());
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::getResizeHeight
     */
    public function testGetResizeHeight()
    {
        $this->assertEquals(50, $this->imagesStorage->getResizeHeight());
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::deleteDirectory
     */
    public function testDeleteDirectoryOverRoot()
    {
        $this->setExpectedException(
            '\Magento\Framework\Exception\LocalizedException',
            sprintf('Directory %s is not under storage root path.', self::INVALID_DIRECTORY_OVER_ROOT)
        );
        $this->imagesStorage->deleteDirectory(self::INVALID_DIRECTORY_OVER_ROOT);
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::deleteDirectory
     */
    public function testDeleteRootDirectory()
    {
        $this->setExpectedException(
            '\Magento\Framework\Exception\LocalizedException',
            sprintf('We can\'t delete root directory %s right now.', self::STORAGE_ROOT_DIR)
        );
        $this->imagesStorage->deleteDirectory(self::STORAGE_ROOT_DIR);
    }

    public function testGetDirsCollectionCreateSubDirectories()
    {
        $directoryName = 'test1';

        $this->coreFileStorageMock->expects($this->once())
            ->method('checkDbUsage')
            ->willReturn(true);

        $this->directoryCollectionMock->expects($this->once())
            ->method('getSubdirectories')
            ->with(self::STORAGE_ROOT_DIR)
            ->willReturn([['name' => $directoryName]]);

        $this->directoryDatabaseFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->directoryCollectionMock);

        $this->directoryMock->expects($this->once())
            ->method('create')
            ->with(rtrim(self::STORAGE_ROOT_DIR, '/') . '/' . $directoryName);

        $this->generalTestGetDirsCollection(self::STORAGE_ROOT_DIR);
    }

    /**
     * @param array $exclude
     * @param array $include
     * @param array $fileNames
     * @param array $expectedRemoveKeys
     * @dataProvider dirsCollectionDataProvider
     */
    public function testGetDirsCollection($exclude, $include, $fileNames, $expectedRemoveKeys)
    {
        $this->imagesStorage = $this->objectManagerHelper->getObject(
            'Magento\Cms\Model\Wysiwyg\Images\Storage',
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false),
                'storageCollectionFactory' => $this->storageCollectionFactoryMock,
                'storageFileFactory' => $this->storageFileFactoryMock,
                'storageDatabaseFactory' => $this->storageDatabaseFactoryMock,
                'directoryDatabaseFactory' => $this->directoryDatabaseFactoryMock,
                'uploaderFactory' => $this->uploaderFactoryMock,
                'resizeParameters' => $this->resizeParameters,
                'dirs' => [
                    'exclude' => $exclude,
                    'include' => $include
                ]
            ]
        );

        $collection = [];
        foreach ($fileNames as $filename) {
            /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
            $objectMock = $this->getMock('Magento\Framework\DataObject', ['getFilename'], [], '', false);
            $objectMock->expects($this->any())
                ->method('getFilename')
                ->willReturn(self::STORAGE_ROOT_DIR . $filename);
            $collection[] = $objectMock;
        }

        $this->generalTestGetDirsCollection(self::STORAGE_ROOT_DIR, $collection, $expectedRemoveKeys);
    }

    /**
     * @return array
     */
    public function dirsCollectionDataProvider()
    {
        return [
            [
                'exclude' => [
                    ['name' => 'dress']
                ],
                'include' => [],
                'filenames' => [],
                'expectRemoveKeys' => []
            ],
            [
                'exclude' => [],
                'include' => [],
                'filenames' => [
                    '/dress',
                ],
                'expectRemoveKeys' => []
            ],
            [
                'exclude' => [
                    ['name' => 'dress']
                ],
                'include' => [],
                'filenames' => [
                    '/collection',
                ],
                'expectRemoveKeys' => []
            ],
            [
                'exclude' => [
                    ['name' => 'gear', 'regexp' => 1],
                    ['name' => 'home', 'regexp' => 1],
                    ['name' => 'collection'],
                    ['name' => 'dress']
                ],
                'include' => [
                    ['name' => 'home', 'regexp' => 1],
                    ['name' => 'collection']
                ],
                'filenames' => [
                    '/dress',
                    '/collection',
                    '/gear'
                ],
                'expectRemoveKeys' => [[0], [2]]
            ]
        ];
    }

    /**
     * General conditions for testGetDirsCollection tests
     *
     * @param string $path
     * @param array $collectionArray
     * @param array $expectedRemoveKeys
     */
    protected function generalTestGetDirsCollection($path, $collectionArray = [], $expectedRemoveKeys = [])
    {
        /** @var StorageCollection|\PHPUnit_Framework_MockObject_MockObject $storageCollectionMock */
        $storageCollectionMock = $this->getMockBuilder('Magento\Cms\Model\Wysiwyg\Images\Storage\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $storageCollectionMock->expects($this->once())
            ->method('setCollectDirs')
            ->with(true)
            ->willReturnSelf();
        $storageCollectionMock->expects($this->once())
            ->method('setCollectFiles')
            ->with(false)
            ->willReturnSelf();
        $storageCollectionMock->expects($this->once())
            ->method('setCollectRecursively')
            ->with(false)
            ->willReturnSelf();
        $storageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collectionArray));
        $storageCollectionInvMock = $storageCollectionMock->expects($this->exactly(sizeof($expectedRemoveKeys)))
            ->method('removeItemByKey');
        call_user_func_array([$storageCollectionInvMock, 'withConsecutive'], $expectedRemoveKeys);

        $this->storageCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($storageCollectionMock);

        $this->imagesStorage->getDirsCollection($path);
    }
}
