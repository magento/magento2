<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Storage model test
 */
namespace Magento\Theme\Test\Unit\Model\Wysiwyg;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_storageRoot;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Theme\Helper\Storage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperStorage;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var null|\Magento\Theme\Model\Wysiwyg\Storage
     */
    protected $_storageModel;

    /**
     * @var \Magento\Framework\Image\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryWrite;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlDecoder;

    protected function setUp()
    {
        $this->_filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->_helperStorage = $this->getMock(\Magento\Theme\Helper\Storage::class, [], [], '', false);
        $this->_objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_imageFactory = $this->getMock(\Magento\Framework\Image\AdapterFactory::class, [], [], '', false);
        $this->directoryWrite = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            [],
            [],
            '',
            false
        );
        $this->urlEncoder = $this->getMock(\Magento\Framework\Url\EncoderInterface::class, ['encode'], [], '', false);
        $this->urlDecoder = $this->getMock(\Magento\Framework\Url\DecoderInterface::class, ['decode'], [], '', false);

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->directoryWrite)
        );

        $this->_storageModel = new \Magento\Theme\Model\Wysiwyg\Storage(
            $this->_filesystem,
            $this->_helperStorage,
            $this->_objectManager,
            $this->_imageFactory,
            $this->urlEncoder,
            $this->urlDecoder
        );

        $this->_storageRoot = '/root';
    }

    protected function tearDown()
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

        $uploader->expects($this->once())->method('save')->will($this->returnValue(['not_empty']));

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getStorageType'
        )->will(
            $this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE)
        );

        /** Prepare filesystem */

        $this->directoryWrite->expects($this->any())->method('isFile')->will($this->returnValue(true));

        $this->directoryWrite->expects($this->once())->method('isReadable')->will($this->returnValue(true));

        /** Prepare image */

        $image = $this->getMock(\Magento\Framework\Image\Adapter\Gd2::class, [], [], '', false);

        $image->expects($this->once())->method('open')->will($this->returnValue(true));

        $image->expects($this->once())->method('keepAspectRatio')->will($this->returnValue(true));

        $image->expects($this->once())->method('resize')->will($this->returnValue(true));

        $image->expects($this->once())->method('save')->will($this->returnValue(true));

        $this->_imageFactory->expects($this->at(0))->method('create')->will($this->returnValue($image));

        /** Prepare session */

        $session = $this->getMock(\Magento\Backend\Model\Session::class, [], [], '', false);

        $this->_helperStorage->expects($this->any())->method('getSession')->will($this->returnValue($session));

        $expectedResult = [
            'not_empty',
            'cookie' => ['name' => null, 'value' => null, 'lifetime' => null, 'path' => null, 'domain' => null],
        ];

        $this->assertEquals($expectedResult, $this->_storageModel->uploadFile($this->_storageRoot));
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::uploadFile
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testUploadInvalidFile()
    {
        $uplaoder = $this->_prepareUploader();

        $uplaoder->expects($this->once())->method('save')->will($this->returnValue(null));

        $this->_storageModel->uploadFile($this->_storageRoot);
    }

    protected function _prepareUploader()
    {
        $uploader = $this->getMock(\Magento\MediaStorage\Model\File\Uploader::class, [], [], '', false);

        $this->_objectManager->expects($this->once())->method('create')->will($this->returnValue($uploader));

        $uploader->expects($this->once())->method('setAllowedExtensions')->will($this->returnValue($uploader));

        $uploader->expects($this->once())->method('setAllowRenameFiles')->will($this->returnValue($uploader));

        $uploader->expects($this->once())->method('setFilesDispersion')->will($this->returnValue($uploader));

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
        )->will(
            $this->returnValue($isWritable)
        );

        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $fullNewPath
        )->will(
            $this->returnValue(false)
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getShortFilename'
        )->with(
            $newDirectoryName
        )->will(
            $this->returnValue($newDirectoryName)
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'convertPathToId'
        )->with(
            $fullNewPath
        )->will(
            $this->returnValue($newDirectoryName)
        );

        $this->_helperStorage->expects(
            $this->any()
        )->method(
            'getStorageRoot'
        )->will(
            $this->returnValue($this->_storageRoot)
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateFolderWithInvalidName()
    {
        $newDirectoryName = 'dir2!#$%^&';
        $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::createFolder
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateFolderDirectoryAlreadyExist()
    {
        $newDirectoryName = 'mew';
        $fullNewPath = $this->_storageRoot . '/' . $newDirectoryName;

        $this->directoryWrite->expects(
            $this->any()
        )->method(
            'isWritable'
        )->with(
            $this->_storageRoot
        )->will(
            $this->returnValue(true)
        );

        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $fullNewPath
        )->will(
            $this->returnValue(true)
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
        )->will(
            $this->returnValue(true)
        );

        $this->directoryWrite->expects($this->once())->method('search')->will($this->returnValue($dirs));

        $this->directoryWrite->expects($this->any())->method('isDirectory')->will($this->returnValue(true));

        $this->assertEquals($dirs, $this->_storageModel->getDirsCollection($this->_storageRoot));
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::getDirsCollection
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetDirsCollectionWrongDirName()
    {
        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $this->_storageRoot
        )->will(
            $this->returnValue(false)
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
        )->will(
            $this->returnValue($this->_storageRoot)
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getStorageType'
        )->will(
            $this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT)
        );

        $this->_helperStorage->expects($this->any())->method('urlEncode')->will($this->returnArgument(0));

        $paths = [$this->_storageRoot . '/' . 'font1.ttf', $this->_storageRoot . '/' . 'font2.ttf'];

        $this->directoryWrite->expects($this->once())->method('search')->will($this->returnValue($paths));

        $this->directoryWrite->expects($this->any())->method('isFile')->will($this->returnValue(true));

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
        )->will(
            $this->returnValue($this->_storageRoot)
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getStorageType'
        )->will(
            $this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE)
        );

        $this->_helperStorage->expects($this->any())->method('urlEncode')->will($this->returnArgument(0));

        $paths = [$this->_storageRoot . '/picture1.jpg'];

        $this->directoryWrite->expects($this->once())->method('search')->will($this->returnValue($paths));

        $this->directoryWrite->expects(
            $this->once()
        )->method(
            'isFile'
        )->with(
            $this->_storageRoot . '/picture1.jpg'
        )->will(
            $this->returnValue(true)
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
        )->will(
            $this->returnValue(true)
        );

        $this->directoryWrite->expects($this->once())->method('search')->will($this->returnValue($dirs));

        $this->directoryWrite->expects($this->any())->method('isDirectory')->will($this->returnValue(true));

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getCurrentPath'
        )->will(
            $this->returnValue($currentPath)
        );

        $this->_helperStorage->expects($this->any())->method('getShortFilename')->will($this->returnArgument(0));

        $this->_helperStorage->expects($this->any())->method('convertPathToId')->will($this->returnArgument(0));

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
            ->will($this->returnValue($this->_storageRoot));

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
        )->will(
            $this->returnValue($this->_storageRoot)
        );

        $this->directoryWrite->expects($this->once())->method('delete')->with($directoryPath);

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    /**
     * cover \Magento\Theme\Model\Wysiwyg\Storage::deleteDirectory
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testDeleteRootDirectory()
    {
        $directoryPath = $this->_storageRoot;

        $this->_helperStorage->expects(
            $this->atLeastOnce()
        )->method(
            'getStorageRoot'
        )->will(
            $this->returnValue($this->_storageRoot)
        );

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    public function booleanCasesDataProvider()
    {
        return [[true], [false]];
    }
}
