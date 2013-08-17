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
 * @category    Mage
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Storage model test
 */
class Mage_Theme_Model_Wysiwyg_StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_storageRoot;

    /**
     * @var Magento_Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var Mage_Theme_Helper_Storage
     */
    protected $_helperStorage;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var null|Mage_Theme_Model_Wysiwyg_Storage
     */
    protected $_storageModel;

    public function setUp()
    {
        $this->_filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);
        $this->_helperStorage = $this->getMock('Mage_Theme_Helper_Storage', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);
        $this->_imageFactory = $this->getMock('Mage_Core_Model_Image_AdapterFactory', array(), array(), '', false);

        $this->_storageModel = new Mage_Theme_Model_Wysiwyg_Storage(
            $this->_filesystem,
            $this->_helperStorage,
            $this->_objectManager,
            $this->_imageFactory
        );

        $this->_storageRoot = Magento_Filesystem::DIRECTORY_SEPARATOR . 'root';
    }

    public function tearDown()
    {
        $this->_filesystem = null;
        $this->_helperStorage = null;
        $this->_objectManager = null;
        $this->_storageModel = null;
        $this->_storageRoot = null;
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::_createThumbnail
     * @covers Mage_Theme_Model_Wysiwyg_Storage::uploadFile
     */
    public function testUploadFile()
    {
        $uplaoder = $this->_prepareUploader();

        $uplaoder->expects($this->once())
            ->method('save')
            ->will($this->returnValue(array('not_empty')));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageType')
            ->will($this->returnValue(Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE));

        /** Prepare filesystem */

        $this->_filesystem->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));


        /** Prepare image */

        $image = $this->getMock('Varien_Image_Adapter_Gd2', array(), array(), '', false);

        $image->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));

        $image->expects($this->once())
            ->method('keepAspectRatio')
            ->will($this->returnValue(true));

        $image->expects($this->once())
            ->method('resize')
            ->will($this->returnValue(true));

        $image->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $this->_imageFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($image));

        /** Prepare session */

        $session = $this->getMock('Mage_Backend_Model_Session', array(), array(), '', false);

        $this->_helperStorage->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));

        $expectedResult = array(
            'not_empty',
            'cookie' => array(
                'name'     => null,
                'value'    => null,
                'lifetime' => null,
                'path'     => null,
                'domain'   => null
            )
        );

        $this->assertEquals($expectedResult, $this->_storageModel->uploadFile($this->_storageRoot));
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::uploadFile
     * @expectedException Mage_Core_Exception
     */
    public function testUploadInvalidFile()
    {
        $uplaoder = $this->_prepareUploader();

        $uplaoder->expects($this->once())
            ->method('save')
            ->will($this->returnValue(null));

        $this->_storageModel->uploadFile($this->_storageRoot);
    }

    protected function _prepareUploader()
    {
        $uploader = $this->getMock('Mage_Core_Model_File_Uploader', array(), array(), '', false);

        $this->_objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($uploader));

        $uploader->expects($this->once())
            ->method('setAllowedExtensions')
            ->will($this->returnValue($uploader));

        $uploader->expects($this->once())
            ->method('setAllowRenameFiles')
            ->will($this->returnValue($uploader));

        $uploader->expects($this->once())
            ->method('setFilesDispersion')
            ->will($this->returnValue($uploader));

        return $uploader;
    }

    /**
     * @dataProvider booleanCasesDataProvider
     * @covers Mage_Theme_Model_Wysiwyg_Storage::createFolder
     */
    public function testCreateFolder($isWritable)
    {
        $newDirectoryName = 'dir1';
        $fullNewPath = $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . $newDirectoryName;

        $this->_filesystem->expects($this->once())
            ->method('isWritable')
            ->with($this->_storageRoot)
            ->will($this->returnValue($isWritable));

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($fullNewPath)
            ->will($this->returnValue(false));

        $this->_filesystem->expects($this->once())
            ->method('ensureDirectoryExists')
            ->with($fullNewPath);


        $this->_helperStorage->expects($this->once())
            ->method('getShortFilename')
            ->with($newDirectoryName)
            ->will($this->returnValue($newDirectoryName));

        $this->_helperStorage->expects($this->once())
            ->method('convertPathToId')
            ->with($fullNewPath)
            ->will($this->returnValue($newDirectoryName));

        $this->_helperStorage->expects($this->any())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));

        $expectedResult = array(
            'name'       => $newDirectoryName,
            'short_name' => $newDirectoryName,
            'path'       => Magento_Filesystem::DIRECTORY_SEPARATOR . $newDirectoryName,
            'id'         => $newDirectoryName
        );

        $this->assertEquals(
            $expectedResult,
            $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot)
        );
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::createFolder
     * @expectedException Mage_Core_Exception
     */
    public function testCreateFolderWithInvalidName()
    {
        $newDirectoryName = 'dir2!#$%^&';
        $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::createFolder
     * @expectedException Mage_Core_Exception
     */
    public function testCreateFolderDirectoryAlreadyExist()
    {
        $newDirectoryName = 'mew';
        $fullNewPath = $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . $newDirectoryName;

        $this->_filesystem->expects($this->once())
            ->method('isWritable')
            ->with($this->_storageRoot)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($fullNewPath)
            ->will($this->returnValue(true));

        $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getDirsCollection
     */
    public function testGetDirsCollection()
    {
        $dirs = array(
            $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'dir1',
            $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'dir2'
        );

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($this->_storageRoot)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->once())
            ->method('searchKeys')
            ->with($this->_storageRoot, '*')
            ->will($this->returnValue($dirs));

        $this->_filesystem->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));

        $this->assertEquals($dirs, $this->_storageModel->getDirsCollection($this->_storageRoot));
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getDirsCollection
     * @expectedException Mage_Core_Exception
     */
    public function testGetDirsCollectionWrongDirName()
    {
        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($this->_storageRoot)
            ->will($this->returnValue(false));

        $this->_storageModel->getDirsCollection($this->_storageRoot);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getFilesCollection
     */
    public function testGetFilesCollection()
    {
        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($this->_storageRoot));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageType')
            ->will($this->returnValue(Mage_Theme_Model_Wysiwyg_Storage::TYPE_FONT));

        $this->_helperStorage->expects($this->any())
            ->method('urlEncode')
            ->will($this->returnArgument(0));


        $paths = array(
            $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'font1.ttf',
            $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'font2.ttf'
        );

        $this->_filesystem->expects($this->once())
            ->method('searchKeys')
            ->with($this->_storageRoot, '*')
            ->will($this->returnValue($paths));

        $this->_filesystem->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));

        $result = $this->_storageModel->getFilesCollection();

        $this->assertCount(2, $result);
        $this->assertEquals('font1.ttf', $result[0]['text']);
        $this->assertEquals('font2.ttf', $result[1]['text']);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getFilesCollection
     */
    public function testGetFilesCollectionImageType()
    {
        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($this->_storageRoot));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageType')
            ->will($this->returnValue(Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE));

        $this->_helperStorage->expects($this->any())
            ->method('urlEncode')
            ->will($this->returnArgument(0));

        $paths = array(
            $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'picture1.jpg'
        );

        $this->_filesystem->expects($this->once())
            ->method('searchKeys')
            ->with($this->_storageRoot, '*')
            ->will($this->returnValue($paths));

        $this->_filesystem->expects($this->once())
            ->method('isFile')
            ->with($this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'picture1.jpg')
            ->will($this->returnValue(true));

        $result = $this->_storageModel->getFilesCollection();

        $this->assertCount(1, $result);
        $this->assertEquals('picture1.jpg', $result[0]['text']);
        $this->assertEquals('picture1.jpg', $result[0]['thumbnailParams']['file']);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getTreeArray
     */
    public function testTreeArray()
    {
        $currentPath = $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'dir';
        $dirs = array(
            $currentPath . Magento_Filesystem::DIRECTORY_SEPARATOR . 'dir_one',
            $currentPath . Magento_Filesystem::DIRECTORY_SEPARATOR . 'dir_two'
        );

        $expectedResult = array(
            array(
                'text' => pathinfo($dirs[0], PATHINFO_BASENAME),
                'id'   => $dirs[0],
                'cls'  => 'folder'
            ),
            array(
                'text' => pathinfo($dirs[1], PATHINFO_BASENAME),
                'id'   => $dirs[1],
                'cls'  => 'folder'
        ));

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($currentPath)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->once())
            ->method('searchKeys')
            ->with($currentPath, '*')
            ->will($this->returnValue($dirs));

        $this->_filesystem->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));


        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($currentPath));

        $this->_helperStorage->expects($this->any())
            ->method('getShortFilename')
            ->will($this->returnArgument(0));

        $this->_helperStorage->expects($this->any())
            ->method('convertPathToId')
            ->will($this->returnArgument(0));

        $result = $this->_storageModel->getTreeArray();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::deleteFile
     */
    public function testDeleteFile()
    {
        $image = 'image.jpg';
        $storagePath = $this->_storageRoot;
        $imagePath = $storagePath . Magento_Filesystem::DIRECTORY_SEPARATOR . $image;
        $thumbnailDir = $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR
            . Mage_Theme_Model_Wysiwyg_Storage::THUMBNAIL_DIRECTORY;

        $session = $this->getMock('Mage_Backend_Model_Session', array('getStoragePath'), array(), '', false);
        $session->expects($this->atLeastOnce())
            ->method('getStoragePath')
            ->will($this->returnValue($storagePath));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($session));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('urlDecode')
            ->with($image)
            ->will($this->returnArgument(0));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getThumbnailDirectory')
            ->with($imagePath)
            ->will($this->returnValue($thumbnailDir));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));


        $filesystem = $this->_filesystem;
        $filesystem::staticExpects($this->once())
            ->method('normalizePath')
            ->with($imagePath)
            ->will($this->returnValue($imagePath));

        $this->_filesystem->expects($this->any())
            ->method('isPathInDirectory')
            ->with($imagePath, $storagePath)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->any())
            ->method('isPathInDirectory')
            ->with($imagePath, $this->_storageRoot)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->at(2))
            ->method('delete')
            ->with($imagePath);

        $this->_filesystem->expects($this->at(3))
            ->method('delete')
            ->with($thumbnailDir . Magento_Filesystem::DIRECTORY_SEPARATOR . $image);

        $this->assertInstanceOf('Mage_Theme_Model_Wysiwyg_Storage', $this->_storageModel->deleteFile($image));
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::deleteDirectory
     */
    public function testDeleteDirectory()
    {
        $directoryPath = $this->_storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . '..'
            . Magento_Filesystem::DIRECTORY_SEPARATOR . 'root';

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));

        $this->_filesystem->expects($this->once())
            ->method('delete')
            ->with($directoryPath);

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::deleteDirectory
     * @expectedException Mage_Core_Exception
     */
    public function testDeleteRootDirectory()
    {
        $directoryPath = $this->_storageRoot;

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    public function booleanCasesDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }
}
