<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var array
     */
    private $allowedImageExtensions = [
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/png',
    ];

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->filesystemMock = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->driverMock = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\DriverInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getRealPath']
        );
        $this->driverMock->expects($this->any())->method('getRealPath')->willReturnArgument(0);

        $this->directoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['delete', 'getDriver', 'create', 'getRelativePath', 'isExist', 'isFile'],
            [],
            '',
            false
        );
        $this->directoryMock->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->driverMock);

        $this->filesystemMock = $this->getMock(
            \Magento\Framework\Filesystem::class,
            ['getDirectoryWrite'],
            [],
            '',
            false
        );
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->adapterFactoryMock = $this->getMock(
            \Magento\Framework\Image\AdapterFactory::class,
            [],
            [],
            '',
            false
        );
        $this->imageHelperMock = $this->getMock(
            \Magento\Cms\Helper\Wysiwyg\Images::class,
            ['getStorageRoot'],
            [],
            '',
            false
        );
        $this->imageHelperMock->expects($this->any())
            ->method('getStorageRoot')
            ->willReturn(self::STORAGE_ROOT_DIR);

        $this->resizeParameters = ['width' => 100, 'height' => 50];

        $this->storageCollectionFactoryMock = $this->getMock(
            \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->storageFileFactoryMock = $this->getMock(
            \Magento\MediaStorage\Model\File\Storage\FileFactory::class,
            [],
            [],
            '',
            false
        );
        $this->storageDatabaseFactoryMock = $this->getMock(
            \Magento\MediaStorage\Model\File\Storage\DatabaseFactory::class,
            [],
            [],
            '',
            false
        );
        $this->directoryDatabaseFactoryMock = $this->getMock(
            \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->directoryCollectionMock = $this->getMock(
            \Magento\MediaStorage\Model\File\Storage\Directory\Database::class,
            [],
            [],
            '',
            false
        );

        $this->uploaderFactoryMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendUrlMock = $this->getMock(\Magento\Backend\Model\Url::class, [], [], '', false);

        $this->coreFileStorageMock = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $allowedExtensions = [
            'allowed' => $this->allowedImageExtensions,
            'image_allowed' => $this->allowedImageExtensions,
        ];

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->imagesStorage = $this->objectManagerHelper->getObject(
            \Magento\Cms\Model\Wysiwyg\Images\Storage::class,
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->getMock(\Magento\Framework\View\Asset\Repository::class, [], [], '', false),
                'storageCollectionFactory' => $this->storageCollectionFactoryMock,
                'storageFileFactory' => $this->storageFileFactoryMock,
                'storageDatabaseFactory' => $this->storageDatabaseFactoryMock,
                'directoryDatabaseFactory' => $this->directoryDatabaseFactoryMock,
                'uploaderFactory' => $this->uploaderFactoryMock,
                'resizeParameters' => $this->resizeParameters,
                'dirs' => [
                    'exclude' => [],
                    'include' => [],
                ],
                'extensions' => $allowedExtensions,
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
            \Magento\Framework\Exception\LocalizedException::class,
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
            \Magento\Framework\Exception\LocalizedException::class,
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
    public function testGetDirsCollection(array $exclude, array $include, array $fileNames, array $expectedRemoveKeys)
    {
        $this->imagesStorage = $this->objectManagerHelper->getObject(
            \Magento\Cms\Model\Wysiwyg\Images\Storage::class,
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->getMock(\Magento\Framework\View\Asset\Repository::class, [], [], '', false),
                'storageCollectionFactory' => $this->storageCollectionFactoryMock,
                'storageFileFactory' => $this->storageFileFactoryMock,
                'storageDatabaseFactory' => $this->storageDatabaseFactoryMock,
                'directoryDatabaseFactory' => $this->directoryDatabaseFactoryMock,
                'uploaderFactory' => $this->uploaderFactoryMock,
                'resizeParameters' => $this->resizeParameters,
                'dirs' => [
                    'exclude' => $exclude,
                    'include' => $include,
                ],
            ]
        );

        $collection = [];
        foreach ($fileNames as $filename) {
            /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
            $objectMock = $this->getMock(\Magento\Framework\DataObject::class, ['getFilename'], [], '', false);
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
                    ['name' => 'dress'],
                ],
                'include' => [],
                'filenames' => [],
                'expectRemoveKeys' => [],
            ],
            [
                'exclude' => [],
                'include' => [],
                'filenames' => [
                    '/dress',
                ],
                'expectRemoveKeys' => [],
            ],
            [
                'exclude' => [
                    ['name' => 'dress'],
                ],
                'include' => [],
                'filenames' => [
                    '/collection',
                ],
                'expectRemoveKeys' => [],
            ],
            [
                'exclude' => [
                    ['name' => 'gear', 'regexp' => 1],
                    ['name' => 'home', 'regexp' => 1],
                    ['name' => 'collection'],
                    ['name' => 'dress'],
                ],
                'include' => [
                    ['name' => 'home', 'regexp' => 1],
                    ['name' => 'collection'],
                ],
                'filenames' => [
                    '/dress',
                    '/collection',
                    '/gear'
                ],
                'expectRemoveKeys' => [[0], [2]],
            ],
        ];
    }

    /**
     * General conditions for testGetDirsCollection tests
     *
     * @param string $path
     * @param array $collectionArray
     * @param array $expectedRemoveKeys
     */
    protected function generalTestGetDirsCollection($path, array $collectionArray = [], array $expectedRemoveKeys = [])
    {
        /** @var StorageCollection|\PHPUnit_Framework_MockObject_MockObject $storageCollectionMock */
        $storageCollectionMock = $this->getMockBuilder(\Magento\Cms\Model\Wysiwyg\Images\Storage\Collection::class)
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

    /**
     * @return void
     */
    public function testUploadFile()
    {
        $targetPath = '/target/path';
        $fileName = 'image.gif';
        $realPath = $targetPath . '/' . $fileName;
        $thumbnailTargetPath = self::STORAGE_ROOT_DIR . '/.thumbs';
        $thumbnailDestination = $thumbnailTargetPath . '/' . $fileName;
        $type = 'image';
        $result = [
            'result'
        ];
        $uploader = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Uploader::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setAllowedExtensions',
                    'setAllowRenameFiles',
                    'setFilesDispersion',
                    'checkMimeType',
                    'save',
                    'getUploadedFileName',
                ]
            )
            ->getMock();
        $this->uploaderFactoryMock->expects($this->atLeastOnce())->method('create')->with(['fileId' => 'image'])
            ->willReturn($uploader);
        $uploader->expects($this->atLeastOnce())->method('setAllowedExtensions')
            ->with(array_keys($this->allowedImageExtensions))->willReturnSelf();
        $uploader->expects($this->atLeastOnce())->method('setAllowRenameFiles')->with(true)->willReturnSelf();
        $uploader->expects($this->atLeastOnce())->method('setFilesDispersion')->with(false)
            ->willReturnSelf();
        $uploader->expects($this->atLeastOnce())->method('checkMimeType')
            ->with(array_values($this->allowedImageExtensions))->willReturnSelf();
        $uploader->expects($this->atLeastOnce())->method('save')->with($targetPath)->willReturn($result);
        $uploader->expects($this->atLeastOnce())->method('getUploadedFileName')->willReturn($fileName);

        $this->directoryMock->expects($this->atLeastOnce())->method('getRelativePath')->willReturnMap(
            [
                [$realPath, $realPath],
                [$thumbnailTargetPath, $thumbnailTargetPath],
                [$thumbnailDestination, $thumbnailDestination],
            ]
        );
        $this->directoryMock->expects($this->atLeastOnce())->method('isFile')
            ->willReturnMap(
                [
                    [$realPath, true],
                    [$thumbnailDestination, true],
                ]
            );
        $this->directoryMock->expects($this->atLeastOnce())->method('isExist')
            ->willReturnMap(
                [
                    [$realPath, true],
                    [$thumbnailTargetPath, true],
                ]
            );

        $image = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image::class)
            ->disableOriginalConstructor()
            ->setMethods(['open', 'keepAspectRatio', 'resize', 'save'])
            ->getMock();
        $image->expects($this->atLeastOnce())->method('open')->with($realPath);
        $image->expects($this->atLeastOnce())->method('keepAspectRatio')->with(true);
        $image->expects($this->atLeastOnce())->method('resize')->with(100, 50);
        $image->expects($this->atLeastOnce())->method('save')->with($thumbnailDestination);

        $this->adapterFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($image);

        $this->assertEquals($result, $this->imagesStorage->uploadFile($targetPath, $type));
    }
}
