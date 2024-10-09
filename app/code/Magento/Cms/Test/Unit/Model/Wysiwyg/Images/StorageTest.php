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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
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
use Psr\Log\LoggerInterface;

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
    private const STORAGE_ROOT_DIR = '/storage/root/dir/';

    private const INVALID_DIRECTORY_OVER_ROOT = '/storage/some/another/dir';

    /**
     * @var Storage
     */
    private $imagesStorage;

    /**
     * @var MockObject
     */
    private $filesystemMock;

    /**
     * @var MockObject
     */
    private $adapterFactoryMock;

    /**
     * @var MockObject
     */
    private $imageHelperMock;

    /**
     * @var array()
     */
    private $resizeParameters;

    /**
     * @var CollectionFactory|MockObject
     */
    private $storageCollectionFactoryMock;

    /**
     * @var FileFactory|MockObject
     */
    private $storageFileFactoryMock;

    /**
     * @var DatabaseFactory|MockObject
     */
    private $storageDatabaseFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory|MockObject
     */
    private $directoryDatabaseFactoryMock;

    /**
     * @var Database|MockObject
     */
    private $directoryCollectionMock;

    /**
     * @var UploaderFactory|MockObject
     */
    private $uploaderFactoryMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var Url|MockObject
     */
    private $backendUrlMock;

    /**
     * @var Write|MockObject
     */
    private $directoryMock;

    /**
     * @var DriverInterface|MockObject
     */
    private $driverMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|MockObject
     */
    private $coreFileStorageMock;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManagerHelper;

    /**
     * @var File|MockObject
     */
    private $ioFileMock;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|MockObject
     */
    private $fileMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject
     */
    private $coreConfigMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->driverMock = $this->getMockBuilder(DriverInterface::class)
            ->onlyMethods(['getRealPathSafety'])
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
            ['getStorageRoot', 'getCurrentPath', 'getCurrentUrl']
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

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetRepo = $this->createMock(Repository::class);

        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getCurrentPath'])
            ->onlyMethods(
                [
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

        $this->coreConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config = [
            'target',
            'folder1',
            'folder2/subfolder21',
            'folder2/subfolder22',
            'folder3/subfolder31/subfolder32'
        ];
        $this->coreConfigMock->expects($this->any())
            ->method('getValue')
            ->with('system/media_storage_configuration/allowed_resources/media_gallery_image_folders')
            ->willReturn($config);

        $this->imagesStorage = $this->objectManagerHelper->getObject(
            Storage::class,
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->assetRepo,
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
                'ioFile' => $this->ioFileMock,
                'coreConfig' => $this->coreConfigMock,
                'logger' => $this->loggerMock
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
        $this->expectExceptionMessage('We cannot delete the selected directory.');
        $this->imagesStorage->deleteDirectory(self::INVALID_DIRECTORY_OVER_ROOT);
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Images\Storage::deleteDirectory
     */
    public function testDeleteRootDirectory()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('We cannot delete the selected directory.');
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

        $this->generalTestGetDirsCollection(
            self::STORAGE_ROOT_DIR,
            1,
            '/^(target|folder1|folder2|folder3)$/'
        );
    }

    /**
     * Test getFilesCollection() with the set of valid and invalid files
     *
     * @return void
     * @throws LocalizedException
     * @throws FileSystemException
     * @dataProvider fileItemsDataProvider
     */
    public function testGetFilesCollection(
        int $timesWarningTriggered,
        string $thumbnailPath,
        DataObject $imageItem
    ) {
        /** @var StorageCollection|MockObject $storageCollectionMock */
        $storageCollectionMock = $this->getMockBuilder(StorageCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storageCollectionMock->expects($this->once())
            ->method('setCollectDirs')
            ->willReturnSelf();
        $storageCollectionMock->expects($this->once())
            ->method('setCollectFiles')
            ->willReturnSelf();
        $storageCollectionMock->expects($this->once())
            ->method('setCollectRecursively')
            ->willReturnSelf();
        $storageCollectionMock->expects($this->once())
            ->method('setOrder')
            ->willReturnSelf();
        $storageCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$imageItem]));

        $this->storageCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($storageCollectionMock);

        $this->driverMock->expects(self::once())
            ->method('stat')
            ->willReturn($imageItem->toArray());

        $this->assetRepo->expects($this->exactly($timesWarningTriggered))
            ->method('getUrl')
            ->willReturn($thumbnailPath);

        $this->loggerMock->expects($this->exactly($timesWarningTriggered))
            ->method('warning')
            ->with(
                sprintf(
                    "The image %s is invalid and cannot be displayed in the gallery.",
                    $imageItem->getBasename()
                )
            );

        $this->imagesStorage->getFilesCollection('/webroot/pub/media/', 'image');
    }

    /**
     * Returns a set of valid and invalid image files
     *
     * @return array[]
     */
    public static function fileItemsDataProvider()
    {
        return [
            // Images files with the size of 0 bytes should generate proper warnings
            [
                'timesWarningTriggered' => 1,
                'thumbnailPath' => Storage::THUMB_PLACEHOLDER_PATH_SUFFIX,
                'imageItem' =>
                    new DataObject(
                        [
                            'mtime' => 0,
                            'size' => 0,
                            'filename' => '/webroot/pub/media/wysiwyg/zero-bytes.jpg',
                            'basename' => 'zero-bytes.jpg',
                            'id' => 1,
                            'name' => 'zero-bytes.jpg',
                            'short_name' => 'zero-bytes.jpg',
                            'url' => 'https://magento.local/pub/media/wysiwyg/zero-bytes.jpg',
                            'mime_type' => 'image/jpeg'
                        ]
                    )
            ],
            // Images files with incorrect not allowed extensions should generate proper warnings
            [
                'timesWarningTriggered' => 1,
                'thumbnailPath' => Storage::THUMB_PLACEHOLDER_PATH_SUFFIX,
                'imageItem' =>
                    new DataObject(
                        [
                            'mtime' => 0,
                            'size' => 1024,
                            'filename' => '/webroot/pub/media/wysiwyg/wrong-image.exe',
                            'basename' => 'wrong-image.exe',
                            'id' => 1,
                            'name' => 'wrong-image.exe',
                            'short_name' => 'wrong-image.exe',
                            'url' => 'https://magento.local/pub/media/wysiwyg/wrong-image.exe',
                            'mime_type' => 'image/jpeg'
                        ]
                    )
            ],
            // Images with non-zero size and allowed extension should not generate warnings
            [
                'timesWarningTriggered' => 0,
                'thumbnailPath' => '',
                'imageItem' =>
                    new DataObject(
                        [
                            'mtime' => 0,
                            'size' => 1024,
                            'filename' => '/webroot/pub/media/wysiwyg/image.jpg',
                            'basename' => 'image.jpg',
                            'id' => 1,
                            'name' => 'image.jpg',
                            'short_name' => 'image.jpg',
                            'url' => 'https://magento.local/pub/media/wysiwyg/image.jpg',
                            'mime_type' => 'image/jpeg'
                        ]
                    )
            ],
        ];
    }

    /**
     * @param $path
     * @param $callNum
     * @param string $dirsFilter
     * @throws \Exception
     * @dataProvider dirsCollectionDataProvider
     */
    public function testGetDirsCollection($path, $callNum, $dirsFilter = '')
    {
        $this->generalTestGetDirsCollection($path, $callNum, $dirsFilter);
    }

    /**
     * @return array
     */
    public static function dirsCollectionDataProvider()
    {
        return [
            [
                'path' => self::STORAGE_ROOT_DIR,
                'callNum' => 1,
                'dirsFilter' => '/^(target|folder1|folder2|folder3)$/'
            ],
            [
                'path' => self::STORAGE_ROOT_DIR . 'target',
                'callNum' => 0,
            ],
            [
                'path' => self::STORAGE_ROOT_DIR . 'folder1/subfolder',
                'callNum' => 0,
            ],
            [
                'path' => self::STORAGE_ROOT_DIR . 'folder2',
                'callNum' => 1,
                'dirsFilter' => '/^(subfolder21|subfolder22)$/'
            ],
            [
                'path' => self::STORAGE_ROOT_DIR . 'folder3/subfolder31',
                'callNum' => 1,
                'dirsFilter' => '/^(subfolder32)$/'
            ],
            [
                'path' => self::STORAGE_ROOT_DIR . 'folder3/subfolder31/subfolder32',
                'callNum' => 0,
            ],
            [
                'path' => self::STORAGE_ROOT_DIR . 'unknown',
                'callNum' => 1,
                'dirsFilter' => '/^()$/'
            ],
        ];
    }

    /**
     * General conditions for testGetDirsCollection tests
     *
     * @param string $path
     * @param int $callNum
     * @param string $dirsFilter
     * @throws \Exception
     */
    protected function generalTestGetDirsCollection(string $path, int $callNum, string $dirsFilter)
    {
        /** @var StorageCollection|MockObject $storageCollectionMock */
        $storageCollectionMock = $this->getMockBuilder(StorageCollection::class)
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
        $storageCollectionMock->expects($this->exactly($callNum))
            ->method('setDirsFilter')
            ->with($dirsFilter);

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
            ->onlyMethods(
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
        $this->driverMock->expects(self::once())
            ->method('fileGetContents')
            ->willReturn('some content');

        $image = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->addMethods(['open', 'keepAspectRatio'])
            ->onlyMethods(['resize', 'save'])
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
