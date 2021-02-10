<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Wysiwyg\Images;

use Magento\Backend\Model\Session;
use Magento\Backend\Model\Url;
use Magento\Catalog\Model\Product\Image;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Cms\Model\Wysiwyg\Images\Storage\Collection as StorageCollection;
use Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\Repository;
use Magento\MediaStorage\Model\File\Storage\DatabaseFactory;
use Magento\MediaStorage\Model\File\Storage\Directory\Database;
use Magento\MediaStorage\Model\File\Storage\FileFactory;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class StorageTest extends TestCase
{
    /**
     * Directory paths samples
     */
    const STORAGE_ROOT_DIR = '/storage/root/dir/';

    const INVALID_DIRECTORY_OVER_ROOT = '/storage/some/another/dir';

    /**
     * @var Storage
     */
    protected $imagesStorage;

    /**
     * @var MockObject
     */
    protected $filesystemMock;

    /**
     * @var MockObject
     */
    protected $adapterFactoryMock;

    /**
     * @var MockObject
     */
    protected $imageHelperMock;

    /**
     * @var array()
     */
    protected $resizeParameters;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $storageCollectionFactoryMock;

    /**
     * @var FileFactory|MockObject
     */
    protected $storageFileFactoryMock;

    /**
     * @var DatabaseFactory|MockObject
     */
    protected $storageDatabaseFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory|MockObject
     */
    protected $directoryDatabaseFactoryMock;

    /**
     * @var Database|MockObject
     */
    protected $directoryCollectionMock;

    /**
     * @var UploaderFactory|MockObject
     */
    protected $uploaderFactoryMock;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var Url|MockObject
     */
    protected $backendUrlMock;

    /**
     * @var Write|MockObject
     */
    protected $directoryMock;

    /**
     * @var DriverInterface|MockObject
     */
    protected $driverMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|MockObject
     */
    protected $coreFileStorageMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerHelper;

    /**
     * @var File|MockObject
     */
    protected $ioFileMock;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|MockObject
     */
    private $fileMock;

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
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->driverMock = $this->getMockBuilder(DriverInterface::class)
            ->setMethods(['getRealPathSafety'])
            ->getMockForAbstractClass();

        $this->directoryMock = $this->createPartialMock(
            Write::class,
            ['delete', 'getDriver', 'create', 'getRelativePath', 'getAbsolutePath', 'isExist', 'isFile']
        );
        $this->directoryMock->expects(
            $this->any()
        )->method(
            'getDriver'
        )->willReturn(
            $this->driverMock
        );

        $this->filesystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::MEDIA
        )->willReturn(
            $this->directoryMock
        );

        $this->fileMock   = $this->objectManagerHelper->getObject(\Magento\Framework\Filesystem\Driver\File::class);
        $this->ioFileMock = $this->createPartialMock(File::class, ['getPathInfo']);
        $this->ioFileMock->expects(
            $this->any()
        )->method(
            'getPathInfo'
        )->willReturnCallback(
            function ($path) {
                return pathinfo($path);
            }
        );

        $this->adapterFactoryMock = $this->createMock(AdapterFactory::class);
        $this->imageHelperMock = $this->createPartialMock(
            Images::class,
            ['getStorageRoot', 'getCurrentPath']
        );
        $this->imageHelperMock->expects(
            $this->any()
        )->method(
            'getStorageRoot'
        )->willReturn(
            self::STORAGE_ROOT_DIR
        );

        $this->resizeParameters = ['width' => 100, 'height' => 50];

        $this->storageCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->storageFileFactoryMock = $this->createMock(FileFactory::class);
        $this->storageDatabaseFactoryMock = $this->createMock(
            DatabaseFactory::class
        );
        $this->directoryDatabaseFactoryMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory::class,
            ['create']
        );
        $this->directoryCollectionMock = $this->createMock(
            Database::class
        );

        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(
                [
                    'getCurrentPath',
                    'getName',
                    'getSessionId',
                    'getCookieLifetime',
                    'getCookiePath',
                    'getCookieDomain',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendUrlMock = $this->createMock(Url::class);

        $this->coreFileStorageMock = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $allowedExtensions = [
            'allowed' => $this->allowedImageExtensions,
            'image_allowed' => $this->allowedImageExtensions,
        ];

        $this->imagesStorage = $this->objectManagerHelper->getObject(
            Storage::class,
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->createMock(Repository::class),
                'storageCollectionFactory' => $this->storageCollectionFactoryMock,
                'storageFileFactory' => $this->storageFileFactoryMock,
                'storageDatabaseFactory' => $this->storageDatabaseFactoryMock,
                'directoryDatabaseFactory' => $this->directoryDatabaseFactoryMock,
                'uploaderFactory' => $this->uploaderFactoryMock,
                'resizeParameters' => $this->resizeParameters,
                'extensions' => $allowedExtensions,
                'dirs' => [
                    'exclude' => [],
                    'include' => [],
                ],
                'data' => [],
                'file' => $this->fileMock,
                'ioFile' => $this->ioFileMock
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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Directory /storage/some/another/dir is not under storage root path.');
        $this->driverMock->expects($this->atLeastOnce())->method('getRealPathSafety')->willReturnArgument(0);
        $this->directoryMock->expects($this->atLeastOnce())->method('getAbsolutePath')->willReturnArgument(0);
        $this->imagesStorage->deleteDirectory(self::INVALID_DIRECTORY_OVER_ROOT);
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::deleteDirectory
     */
    public function testDeleteRootDirectory()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('We can\'t delete root directory /storage/root/dir right now.');
        $this->driverMock->expects($this->atLeastOnce())->method('getRealPathSafety')->willReturnArgument(0);
        $this->directoryMock->expects($this->atLeastOnce())->method('getAbsolutePath')->willReturnArgument(0);
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
            Storage::class,
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->createMock(Repository::class),
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
            /** @var DataObject|MockObject $objectMock */
            $objectMock = $this->getMockBuilder(DataObject::class)
                ->addMethods(['getFilename'])
                ->disableOriginalConstructor()
                ->getMock();
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
                    '/gear',
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
    protected function generalTestGetDirsCollection($path, $collectionArray = [], $expectedRemoveKeys = [])
    {
        /** @var StorageCollection|MockObject $storageCollectionMock */
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
            ->method('setOrder')
            ->with('basename', \Magento\Framework\Data\Collection\Filesystem::SORT_ORDER_ASC)
            ->willReturnSelf();
        $storageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collectionArray));
        $storageCollectionInvMock = $storageCollectionMock->expects($this->exactly(count($expectedRemoveKeys)))
            ->method('removeItemByKey');
        call_user_func_array([$storageCollectionInvMock, 'withConsecutive'], $expectedRemoveKeys);

        $this->storageCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($storageCollectionMock);

        $this->imagesStorage->getDirsCollection($path);
    }

    public function testUploadFile()
    {
        $path = 'target/path';
        $targetPath = self::STORAGE_ROOT_DIR . $path;
        $fileName = 'image.gif';
        $realPath = $targetPath . '/' . $fileName;
        $thumbnailTargetPath = self::STORAGE_ROOT_DIR . '.thumbs' . $path;
        $thumbnailDestination = $thumbnailTargetPath . '/' . $fileName;
        $type = 'image';
        $result = [
            'result'
        ];
        $uploader = $this->getMockBuilder(Uploader::class)
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

        $image = $this->getMockBuilder(Image::class)
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

    /**
     * Test create directory with invalid name
     */
    public function testCreateDirectoryWithInvalidName()
    {
        $name = 'папка';
        $path = '/tmp/path';
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            (string)__('Please rename the folder using only Latin letters, numbers, underscores and dashes.')
        );
        $this->imagesStorage->createDirectory($name, $path);
    }
}
