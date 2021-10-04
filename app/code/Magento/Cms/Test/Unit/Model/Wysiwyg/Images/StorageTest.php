<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage\Collection as StorageCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class StorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Directory paths samples
     */
    const STORAGE_ROOT_DIR = '/storage/root/dir/';

    const INVALID_DIRECTORY_OVER_ROOT = '/storage/some/another/dir';

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    private $imagesStorage;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $adapterFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $imageHelperMock;

    /**
     * @var array()
     */
    private $resizeParameters;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storageCollectionFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\FileFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storageFileFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storageDatabaseFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryDatabaseFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\Database|\PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryCollectionMock;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $uploaderFactoryMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionMock;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit\Framework\MockObject\MockObject
     */
    private $backendUrlMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryMock;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $driverMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit\Framework\MockObject\MockObject
     */
    private $coreFileStorageMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Filesystem\Io\File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ioFileMock;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileMock;

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
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->driverMock = $this->getMockBuilder(\Magento\Framework\Filesystem\DriverInterface::class)
            ->setMethods(['getRealPathSafety'])
            ->getMockForAbstractClass();

        $this->directoryMock = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['delete', 'getDriver', 'create', 'getRelativePath', 'getAbsolutePath', 'isExist', 'isFile']
        );
        $this->directoryMock->expects(
            $this->any()
        )->method(
            'getDriver'
        )->willReturn(
            $this->driverMock
        );

        $this->filesystemMock = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite']);
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
        $this->ioFileMock = $this->createPartialMock(\Magento\Framework\Filesystem\Io\File::class, ['getPathInfo']);
        $this->ioFileMock->expects(
            $this->any()
        )->method(
            'getPathInfo'
        )->willReturnCallback(
            function ($path) {
                 return pathinfo($path);
            }
        );

        $this->adapterFactoryMock = $this->createMock(\Magento\Framework\Image\AdapterFactory::class);
        $this->imageHelperMock = $this->createPartialMock(
            \Magento\Cms\Helper\Wysiwyg\Images::class,
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
            \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory::class,
            ['create']
        );
        $this->storageFileFactoryMock = $this->createMock(\Magento\MediaStorage\Model\File\Storage\FileFactory::class);
        $this->storageDatabaseFactoryMock = $this->createMock(
            \Magento\MediaStorage\Model\File\Storage\DatabaseFactory::class
        );
        $this->directoryDatabaseFactoryMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory::class,
            ['create']
        );
        $this->directoryCollectionMock = $this->createMock(
            \Magento\MediaStorage\Model\File\Storage\Directory\Database::class
        );

        $this->uploaderFactoryMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
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
        $this->backendUrlMock = $this->createMock(\Magento\Backend\Model\Url::class);

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
            \Magento\Cms\Model\Wysiwyg\Images\Storage::class,
            [
                'session' => $this->sessionMock,
                'backendUrl' => $this->backendUrlMock,
                'cmsWysiwygImages' => $this->imageHelperMock,
                'coreFileStorageDb' => $this->coreFileStorageMock,
                'filesystem' => $this->filesystemMock,
                'imageFactory' => $this->adapterFactoryMock,
                'assetRepo' => $this->createMock(\Magento\Framework\View\Asset\Repository::class),
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
                'coreConfig' => $this->coreConfigMock
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
     * @param array $exclude
     * @param array $include
     * @param array $fileNames
     * @param array $expectedRemoveKeys
     * @dataProvider dirsCollectionDataProvider
     */
    public function testGetDirsCollection($path, $callNum, $dirsFilter = '')
    {
        $this->generalTestGetDirsCollection($path, $callNum, $dirsFilter);
    }

    /**
     * @return array
     */
    public function dirsCollectionDataProvider()
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
        /** @var StorageCollection|\PHPUnit\Framework\MockObject\MockObject $storageCollectionMock */
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

    /**
     * Test upload file with excessive path
     */
    public function testUploadFileWithExcessivePath()
    {
        $this->expectException(
            \Magento\Framework\Exception\LocalizedException::class
        );

        $path = 'target/path';
        $targetPath = self::STORAGE_ROOT_DIR .str_repeat('a', 255) . $path;
        $type = 'image';
        $this->imagesStorage->uploadFile($targetPath, $type);
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
            (string)__('Please rename the folder using only letters, numbers, underscores and dashes.')
        );
        $this->imagesStorage->createDirectory($name, $path);
    }
}
