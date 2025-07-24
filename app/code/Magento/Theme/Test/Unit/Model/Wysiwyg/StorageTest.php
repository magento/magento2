<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Storage model test
 */
namespace Magento\Theme\Test\Unit\Model\Wysiwyg;

use Magento\Backend\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\Adapter\Gd2;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Theme\Helper\Storage as HelperStorage;
use Magento\Theme\Model\Wysiwyg\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends TestCase
{
    /**
     * @var string
     */
    protected $storageRoot;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var HelperStorage|MockObject
     */
    protected $helperStorage;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var null|Storage
     */
    protected $storageModel;

    /**
     * @var AdapterFactory|MockObject
     */
    protected $imageFactory;

    /**
     * @var Write|MockObject
     */
    protected $directoryWrite;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $urlEncoder;

    /**
     * @var DecoderInterface|MockObject
     */
    protected $urlDecoder;

    /**
     * @var DriverInterface|MockObject
     */
    private $filesystemDriver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                \Magento\Framework\Filesystem\Io\File::class,
                $this->createMock(\Magento\Framework\Filesystem\Io\File::class)
            ],
            [
                DriverInterface::class,
                $this->createMock(DriverInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->filesystem = $this->createMock(Filesystem::class);

        $file = $this->createPartialMock(File::class, ['getPathInfo']);

        $file->expects($this->any())
            ->method('getPathInfo')
            ->willReturnCallback(
                function ($path) {
                    return pathinfo($path);
                }
            );

        $this->helperStorage = $this->getMockBuilder(HelperStorage::class)
            ->addMethods(['urlEncode'])
            ->onlyMethods(
                [
                    'getStorageType',
                    'getCurrentPath',
                    'getStorageRoot',
                    'getShortFilename',
                    'getSession',
                    'convertPathToId',
                    'getRequestParams'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $reflection = new ReflectionClass(HelperStorage::class);
        $reflection_property = $reflection->getProperty('file');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->helperStorage, $file);

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->imageFactory = $this->createMock(AdapterFactory::class);
        $this->directoryWrite = $this->createMock(Write::class);
        $this->urlEncoder = $this->createPartialMock(EncoderInterface::class, ['encode']);
        $this->urlDecoder = $this->createPartialMock(DecoderInterface::class, ['decode']);
        $this->filesystemDriver = $this->createMock(DriverInterface::class);

        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWrite);

        $this->storageModel = new Storage(
            $this->filesystem,
            $this->helperStorage,
            $this->objectManager,
            $this->imageFactory,
            $this->urlEncoder,
            $this->urlDecoder,
            $file,
            $this->filesystemDriver
        );

        $this->storageRoot = '/root';
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->filesystem = null;
        $this->helperStorage = null;
        $this->objectManager = null;
        $this->storageModel = null;
        $this->storageRoot = null;
    }

    /**
     * @return void
     * cover Storage::_createThumbnail
     * cover Storage::uploadFile
     */
    public function testUploadFile(): void
    {
        $uploader = $this->prepareUploader();
        $uploader->expects($this->once())->method('save')->willReturn(['not_empty', 'path' => 'absPath']);
        $this->helperStorage->expects($this->any())
            ->method('getStorageType')
            ->willReturn(Storage::TYPE_IMAGE);

        /** Prepare filesystem */

        $this->directoryWrite->expects($this->any())->method('isFile')->willReturn(true);
        $this->directoryWrite->expects($this->once())->method('isReadable')->willReturn(true);

        /** Prepare image */

        $image = $this->createMock(Gd2::class);

        $image->expects($this->once())->method('open')->willReturn(true);
        $image->expects($this->once())->method('keepAspectRatio')->willReturn(true);
        $image->expects($this->once())->method('resize')->willReturn(true);
        $image->expects($this->once())->method('save')->willReturn(true);

        $this->imageFactory
            ->method('create')
            ->willReturn($image);

        /** Prepare session */

        $session = $this->createMock(Session::class);

        $this->helperStorage->expects($this->any())->method('getSession')->willReturn($session);
        $expectedResult = ['not_empty'];

        $this->assertEquals($expectedResult, $this->storageModel->uploadFile($this->storageRoot));
    }

    /**
     * @return void
     * cover Storage::uploadFile
     */
    public function testUploadInvalidFile(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $uploader = $this->prepareUploader();

        $uploader->expects($this->once())->method('save')->willReturn(null);

        $this->storageModel->uploadFile($this->storageRoot);
    }

    /**
     * @return MockObject
     */
    protected function prepareUploader(): MockObject
    {
        $uploader = $this->createMock(Uploader::class);

        $this->objectManager->expects($this->once())->method('create')->willReturn($uploader);
        $uploader->expects($this->once())->method('setAllowedExtensions')->willReturn($uploader);
        $uploader->expects($this->once())->method('setAllowRenameFiles')->willReturn($uploader);
        $uploader->expects($this->once())->method('setFilesDispersion')->willReturn($uploader);

        return $uploader;
    }

    /**
     * @return void
     * @dataProvider booleanCasesDataProvider
     * cover Storage::createFolder
     */
    public function testCreateFolder($isWritable): void
    {
        $newDirectoryName = 'dir1';
        $fullNewPath = $this->storageRoot . '/' . $newDirectoryName;

        $this->directoryWrite->expects($this->any())
            ->method('isWritable')
            ->with($this->storageRoot)
            ->willReturn($isWritable);

        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($fullNewPath)
            ->willReturn(false);

        $this->helperStorage->expects($this->once())
            ->method('getShortFilename')
            ->with($newDirectoryName)
            ->willReturn($newDirectoryName);

        $this->helperStorage->expects($this->once())
            ->method('convertPathToId')
            ->with($fullNewPath)
            ->willReturn($newDirectoryName);

        $this->helperStorage->expects($this->any())
            ->method('getStorageRoot')
            ->willReturn($this->storageRoot);

        $expectedResult = [
            'name' => $newDirectoryName,
            'short_name' => $newDirectoryName,
            'path' => '/' . $newDirectoryName,
            'id' => $newDirectoryName
        ];

        $this->assertEquals(
            $expectedResult,
            $this->storageModel->createFolder($newDirectoryName, $this->storageRoot)
        );
    }

    /**
     * @return void
     * cover Storage::createFolder
     */
    public function testCreateFolderWithInvalidName(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $newDirectoryName = 'dir2!#$%^&';
        $this->storageModel->createFolder($newDirectoryName, $this->storageRoot);
    }

    /**
     * @return void
     * cover Storage::createFolder
     */
    public function testCreateFolderDirectoryAlreadyExist(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $newDirectoryName = 'mew';
        $fullNewPath = $this->storageRoot . '/' . $newDirectoryName;

        $this->directoryWrite->expects($this->any())
            ->method('isWritable')
            ->with($this->storageRoot)
            ->willReturn(true);

        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($fullNewPath)
            ->willReturn(true);

        $this->storageModel->createFolder($newDirectoryName, $this->storageRoot);
    }

    /**
     * @return void
     * cover Storage::getDirsCollection
     */
    public function testGetDirsCollection(): void
    {
        $dirs = [$this->storageRoot . '/dir1', $this->storageRoot . '/dir2'];

        $this->directoryWrite
            ->method('isExist')
            ->with($this->storageRoot)
            ->willReturn(true);

        $this->directoryWrite->expects($this->once())->method('search')->willReturn($dirs);

        $this->directoryWrite->expects($this->any())->method('isDirectory')->willReturn(true);

        $this->assertEquals($dirs, $this->storageModel->getDirsCollection($this->storageRoot));
    }

    /**
     * @return void
     * cover Storage::getDirsCollection
     */
    public function testGetDirsCollectionWrongDirName(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($this->storageRoot)
            ->willReturn(false);

        $this->storageModel->getDirsCollection($this->storageRoot);
    }

    /**
     * @return void
     * cover Storage::getFilesCollection
     */
    public function testGetFilesCollection(): void
    {
        $this->helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->willReturn($this->storageRoot);

        $this->helperStorage->expects($this->once())
            ->method('getStorageType')
            ->willReturn(Storage::TYPE_FONT);

        $this->helperStorage->expects($this->any())->method('urlEncode')->willReturnArgument(0);
        $paths = [$this->storageRoot . '/' . 'font1.ttf', $this->storageRoot . '/' . 'font2.ttf'];
        $this->directoryWrite->expects($this->once())->method('search')->willReturn($paths);
        $this->directoryWrite->expects($this->any())->method('isFile')->willReturn(true);
        $result = $this->storageModel->getFilesCollection();

        $this->assertCount(2, $result);
        $this->assertEquals('font1.ttf', $result[0]['text']);
        $this->assertEquals('font2.ttf', $result[1]['text']);
    }

    /**
     * @return void
     * cover Storage::getFilesCollection
     */
    public function testGetFilesCollectionImageType(): void
    {
        $this->helperStorage->expects($this->once())->method('getCurrentPath')->willReturn($this->storageRoot);
        $this->helperStorage->expects($this->once())->method('getStorageType')->willReturn(Storage::TYPE_IMAGE);
        $this->helperStorage->expects($this->any())->method('urlEncode')->willReturnArgument(0);

        $paths = [$this->storageRoot . '/picture1.jpg'];
        $this->directoryWrite->expects($this->once())->method('search')->willReturn($paths);
        $this->directoryWrite->expects($this->once())
            ->method('isFile')
            ->with($this->storageRoot . '/picture1.jpg')
            ->willReturn(true);

        $result = $this->storageModel->getFilesCollection();

        $this->assertCount(1, $result);
        $this->assertEquals('picture1.jpg', $result[0]['text']);
        $this->assertEquals('picture1.jpg', $result[0]['thumbnailParams']['file']);
    }

    /**
     * @return void
     * cover Storage::getTreeArray
     */
    public function testTreeArray(): void
    {
        $currentPath = $this->storageRoot . '/dir';
        $dirs = [$currentPath . '/dir_one', $currentPath . '/dir_two'];

        $expectedResult = [
            ['text' => pathinfo($dirs[0], PATHINFO_BASENAME), 'id' => $dirs[0], 'cls' => 'folder'],
            ['text' => pathinfo($dirs[1], PATHINFO_BASENAME), 'id' => $dirs[1], 'cls' => 'folder']
        ];

        $this->directoryWrite->expects($this->once())->method('isExist')->with($currentPath)->willReturn(true);
        $this->directoryWrite->expects($this->once())->method('search')->willReturn($dirs);
        $this->directoryWrite->expects($this->any())->method('isDirectory')->willReturn(true);
        $this->helperStorage->expects($this->once())->method('getCurrentPath')->willReturn($currentPath);
        $this->helperStorage->expects($this->any())->method('getShortFilename')->willReturnArgument(0);
        $this->helperStorage->expects($this->any())->method('convertPathToId')->willReturnArgument(0);

        $result = $this->storageModel->getTreeArray();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return void
     * @cover Storage::deleteFile
     */
    public function testDeleteFile(): void
    {
        $image = 'image.jpg';

        $this->helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->willReturn($this->storageRoot);

        $this->urlDecoder->expects($this->any())
            ->method('decode')
            ->with($image)
            ->willReturnArgument(0);

        $this->directoryWrite
            ->method('getRelativePath')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$this->storageRoot] => $this->storageRoot,
                [$this->storageRoot . '/' . $image] => $this->storageRoot . '/' . $image
            });

        $this->helperStorage->expects($this->once())
            ->method('getStorageRoot')
            ->willReturn('/');

        $this->directoryWrite->expects($this->any())->method('delete');
        $this->assertInstanceOf(Storage::class, $this->storageModel->deleteFile($image));
    }

    /**
     * @return void
     * cover Storage::deleteDirectory
     * @throws LocalizedException
     */
    public function testDeleteDirectory(): void
    {
        $directoryPath = $this->storageRoot . '/../root';

        $this->helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->willReturn($this->storageRoot);
        $this->directoryWrite->expects($this->once())->method('delete')->with($directoryPath);
        $this->directoryWrite->expects($this->once())->method('getAbsolutePath')->willreturn('');
        $this->filesystemDriver->expects($this->once())
            ->method('getRealPathSafety')
            ->with('')
            ->willReturn('');
        $this->storageModel->deleteDirectory($directoryPath);
    }

    /**
     * @return void
     * cover Storage::deleteDirectory
     */
    public function testDeleteRootDirectory(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $directoryPath = $this->storageRoot;

        $this->helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->willReturn($this->storageRoot);
        $this->filesystemDriver->expects($this->once())
            ->method('getRealPathSafety')
            ->with('')
            ->willReturn('');
        $this->storageModel->deleteDirectory($directoryPath);
    }

    /**
     * @return void
     * cover Storage::deleteDirectory
     */
    public function testDeleteRootDirectoryRelative(): void
    {
        $this->expectException(
            LocalizedException::class
        );

        $directoryPath = $this->storageRoot;
        $fakePath = 'fake/relative/path';

        $this->directoryWrite->method('getAbsolutePath')
            ->with($fakePath)
            ->willReturn($directoryPath);

        $this->filesystemDriver->method('getRealPathSafety')
            ->with($directoryPath)
            ->willReturn($directoryPath);

        $this->helperStorage
            ->method('getStorageRoot')
            ->willReturn($directoryPath);

        $this->storageModel->deleteDirectory($fakePath);
    }

    /**
     * @return array
     */
    public static function booleanCasesDataProvider(): array
    {
        return [[true], [false]];
    }
}
