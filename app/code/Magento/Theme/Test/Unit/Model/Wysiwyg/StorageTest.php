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
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\Adapter\Gd2;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Theme\Helper\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends TestCase
{
    /**
     * @var string
     */
    protected $_storageRoot;

    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystem;

    /**
     * @var Storage|MockObject
     */
    protected $_helperStorage;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var null|\Magento\Theme\Model\Wysiwyg\Storage
     */
    protected $_storageModel;

    /**
     * @var AdapterFactory|MockObject
     */
    protected $_imageFactory;

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

    protected function setUp(): void
    {
        $this->_filesystem = $this->createMock(Filesystem::class);

        $file = $this->createPartialMock(File::class, ['getPathInfo']);

        $file->expects($this->any())
            ->method('getPathInfo')
            ->willReturnCallback(
                function ($path) {
                    return pathinfo($path);
                }
            );

        $this->_helperStorage = $this->getMockBuilder(Storage::class)
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

        $reflection = new \ReflectionClass(Storage::class);
        $reflection_property = $reflection->getProperty('file');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->_helperStorage, $file);

        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_imageFactory = $this->createMock(AdapterFactory::class);
        $this->directoryWrite = $this->createMock(Write::class);
        $this->urlEncoder = $this->createPartialMock(EncoderInterface::class, ['encode']);
        $this->urlDecoder = $this->createPartialMock(DecoderInterface::class, ['decode']);
        $this->filesystemDriver = $this->createMock(DriverInterface::class);

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->willReturn(
            $this->directoryWrite
        );

        $this->_storageModel = new \Magento\Theme\Model\Wysiwyg\Storage(
            $this->_filesystem,
            $this->_helperStorage,
            $this->_objectManager,
            $this->_imageFactory,
            $this->urlEncoder,
            $this->urlDecoder,
            null,
            $this->filesystemDriver
        );

        $this->_storageRoot = '/root';
    }

    protected function tearDown(): void
    {
        $this->_filesystem = null;
        $this->_helperStorage = null;
        $this->_objectManager = null;
        $this->_storageModel = null;
        $this->_storageRoot = null;
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::_createThumbnail
     * cover \Magento\Theme\Model\Wysiwyg\Storage::uploadFile
     */
    public function testUploadFile()
    {
        $uploader = $this->_prepareUploader();

        $uploader->expects($this->once())->method('save')->willReturn(['not_empty', 'path' => 'absPath']);

        $this->_helperStorage->expects(
            $this->any()
        )->method(
            'getStorageType'
        )->willReturn(
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
        );

        /** Prepare filesystem */

        $this->directoryWrite->expects($this->any())->method('isFile')->willReturn(true);

        $this->directoryWrite->expects($this->once())->method('isReadable')->willReturn(true);

        /** Prepare image */

        $image = $this->createMock(Gd2::class);

        $image->expects($this->once())->method('open')->willReturn(true);

        $image->expects($this->once())->method('keepAspectRatio')->willReturn(true);

        $image->expects($this->once())->method('resize')->willReturn(true);

        $image->expects($this->once())->method('save')->willReturn(true);

        $this->_imageFactory->expects($this->at(0))->method('create')->willReturn($image);

        /** Prepare session */

        $session = $this->createMock(Session::class);

        $this->_helperStorage->expects($this->any())->method('getSession')->willReturn($session);

        $expectedResult = [
            'not_empty'
        ];

        $this->assertEquals($expectedResult, $this->_storageModel->uploadFile($this->_storageRoot));
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::uploadFile
     */
    public function testUploadInvalidFile()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $uploader = $this->_prepareUploader();

        $uploader->expects($this->once())->method('save')->willReturn(null);

        $this->_storageModel->uploadFile($this->_storageRoot);
    }

    /**
     * @return MockObject
     */
    protected function _prepareUploader()
    {
        $uploader = $this->createMock(Uploader::class);

        $this->_objectManager->expects($this->once())->method('create')->willReturn($uploader);

        $uploader->expects($this->once())->method('setAllowedExtensions')->willReturn($uploader);

        $uploader->expects($this->once())->method('setAllowRenameFiles')->willReturn($uploader);

        $uploader->expects($this->once())->method('setFilesDispersion')->willReturn($uploader);

        return $uploader;
    }

    /**
     * @dataProvider booleanCasesDataProvider
     * cover \Magento\Theme\Model\Wysiwyg\Storage::createFolder
     */
    public function testCreateFolder($isWritable)
    {
        $newDirectoryName = 'dir1';
        $fullNewPath = $this->_storageRoot . '/' . $newDirectoryName;

        $this->directoryWrite->expects(
            $this->any()
        )->method(
            'isWritable'
        )->with(
            $this->_storageRoot
        )->willReturn(
            $isWritable
        );

        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $fullNewPath
        )->willReturn(
            false
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getShortFilename'
        )->with(
            $newDirectoryName
        )->willReturn(
            $newDirectoryName
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'convertPathToId'
        )->with(
            $fullNewPath
        )->willReturn(
            $newDirectoryName
        );

        $this->_helperStorage->expects(
            $this->any()
        )->method(
            'getStorageRoot'
        )->willReturn(
            $this->_storageRoot
        );

        $expectedResult = [
            'name' => $newDirectoryName,
            'short_name' => $newDirectoryName,
            'path' => '/' . $newDirectoryName,
            'id' => $newDirectoryName,
        ];

        $this->assertEquals(
            $expectedResult,
            $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot)
        );
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::createFolder
     */
    public function testCreateFolderWithInvalidName()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $newDirectoryName = 'dir2!#$%^&';
        $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::createFolder
     */
    public function testCreateFolderDirectoryAlreadyExist()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $newDirectoryName = 'mew';
        $fullNewPath = $this->_storageRoot . '/' . $newDirectoryName;

        $this->directoryWrite->expects(
            $this->any()
        )->method(
            'isWritable'
        )->with(
            $this->_storageRoot
        )->willReturn(
            true
        );

        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $fullNewPath
        )->willReturn(
            true
        );

        $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::getDirsCollection
     */
    public function testGetDirsCollection()
    {
        $dirs = [$this->_storageRoot . '/dir1', $this->_storageRoot . '/dir2'];

        $this->directoryWrite->expects(
            $this->any()
        )->method(
            'isExist'
        )->with(
            $this->_storageRoot
        )->willReturn(
            true
        );

        $this->directoryWrite->expects($this->once())->method('search')->willReturn($dirs);

        $this->directoryWrite->expects($this->any())->method('isDirectory')->willReturn(true);

        $this->assertEquals($dirs, $this->_storageModel->getDirsCollection($this->_storageRoot));
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::getDirsCollection
     */
    public function testGetDirsCollectionWrongDirName()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $this->_storageRoot
        )->willReturn(
            false
        );

        $this->_storageModel->getDirsCollection($this->_storageRoot);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::getFilesCollection
     */
    public function testGetFilesCollection()
    {
        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getCurrentPath'
        )->willReturn(
            $this->_storageRoot
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getStorageType'
        )->willReturn(
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT
        );

        $this->_helperStorage->expects($this->any())->method('urlEncode')->willReturnArgument(0);

        $paths = [$this->_storageRoot . '/' . 'font1.ttf', $this->_storageRoot . '/' . 'font2.ttf'];

        $this->directoryWrite->expects($this->once())->method('search')->willReturn($paths);

        $this->directoryWrite->expects($this->any())->method('isFile')->willReturn(true);

        $result = $this->_storageModel->getFilesCollection();

        $this->assertCount(2, $result);
        $this->assertEquals('font1.ttf', $result[0]['text']);
        $this->assertEquals('font2.ttf', $result[1]['text']);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::getFilesCollection
     */
    public function testGetFilesCollectionImageType()
    {
        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getCurrentPath'
        )->willReturn(
            $this->_storageRoot
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getStorageType'
        )->willReturn(
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
        );

        $this->_helperStorage->expects($this->any())->method('urlEncode')->willReturnArgument(0);

        $paths = [$this->_storageRoot . '/picture1.jpg'];

        $this->directoryWrite->expects($this->once())->method('search')->willReturn($paths);

        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isFile'
        )->with(
            $this->_storageRoot . '/picture1.jpg'
        )->willReturn(
            true
        );

        $result = $this->_storageModel->getFilesCollection();

        $this->assertCount(1, $result);
        $this->assertEquals('picture1.jpg', $result[0]['text']);
        $this->assertEquals('picture1.jpg', $result[0]['thumbnailParams']['file']);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::getTreeArray
     */
    public function testTreeArray()
    {
        $currentPath = $this->_storageRoot . '/dir';
        $dirs = [$currentPath . '/dir_one', $currentPath . '/dir_two'];

        $expectedResult = [
            ['text' => pathinfo($dirs[0], PATHINFO_BASENAME), 'id' => $dirs[0], 'cls' => 'folder'],
            ['text' => pathinfo($dirs[1], PATHINFO_BASENAME), 'id' => $dirs[1], 'cls' => 'folder'],
        ];

        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $currentPath
        )->willReturn(
            true
        );

        $this->directoryWrite->expects($this->once())->method('search')->willReturn($dirs);

        $this->directoryWrite->expects($this->any())->method('isDirectory')->willReturn(true);

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getCurrentPath'
        )->willReturn(
            $currentPath
        );

        $this->_helperStorage->expects($this->any())->method('getShortFilename')->willReturnArgument(0);

        $this->_helperStorage->expects($this->any())->method('convertPathToId')->willReturnArgument(0);

        $result = $this->_storageModel->getTreeArray();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @cover \Magento\Theme\Model\Wysiwyg\Storage::deleteFile
     */
    public function testDeleteFile()
    {
        $image = 'image.jpg';

        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->willReturn($this->_storageRoot);

        $this->urlDecoder->expects($this->any())
            ->method('decode')
            ->with($image)
            ->willReturnArgument(0);

        $this->directoryWrite->expects($this->at(0))
            ->method('getRelativePath')
            ->with($this->_storageRoot)
            ->willReturn($this->_storageRoot);

        $this->directoryWrite->expects($this->at(1))
            ->method('getRelativePath')
            ->with($this->_storageRoot . '/' . $image)
            ->willReturn($this->_storageRoot . '/' . $image);

        $this->_helperStorage->expects($this->once())
            ->method('getStorageRoot')
            ->willReturn('/');

        $this->directoryWrite->expects($this->any())->method('delete');
        $this->assertInstanceOf(\Magento\Theme\Model\Wysiwyg\Storage::class, $this->_storageModel->deleteFile($image));
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::deleteDirectory
     */
    public function testDeleteDirectory()
    {
        $directoryPath = $this->_storageRoot . '/../root';

        $this->_helperStorage->expects(
            $this->atLeastOnce()
        )->method(
            'getStorageRoot'
        )->willReturn(
            $this->_storageRoot
        );

        $this->directoryWrite->expects($this->once())->method('delete')->with($directoryPath);

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::deleteDirectory
     */
    public function testDeleteRootDirectory()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $directoryPath = $this->_storageRoot;

        $this->_helperStorage->expects(
            $this->atLeastOnce()
        )->method(
            'getStorageRoot'
        )->willReturn(
            $this->_storageRoot
        );

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::deleteDirectory
     */
    public function testDeleteRootDirectoryRelative()
    {
        $this->expectException(
            \Magento\Framework\Exception\LocalizedException::class
        );

        $directoryPath = $this->_storageRoot;
        $fakePath = 'fake/relative/path';

        $this->directoryWrite->method('getAbsolutePath')
            ->with($fakePath)
            ->willReturn($directoryPath);

        $this->filesystemDriver->method('getRealPathSafety')
            ->with($directoryPath)
            ->willReturn($directoryPath);

        $this->_helperStorage
            ->method('getStorageRoot')
            ->willReturn($directoryPath);

        $this->_storageModel->deleteDirectory($fakePath);
    }

    /**
     * @return array
     */
    public function booleanCasesDataProvider()
    {
        return [[true], [false]];
    }
}
