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
 * Storage helper test
 */
class Mage_Theme_Helper_StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var null|Magento_Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var Mage_Backend_Model_Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var Mage_Core_Model_Theme_FlyweightFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeFactory;

    /**
     * @var Zend_Controller_Request_Http|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var Mage_Theme_Helper_Storage
     */
    protected $_storageHelper;

    /**
     * @var string
     */
    protected $_customizationPath;

    public function setUp()
    {
        $this->_customizationPath = Magento_Filesystem::DIRECTORY_SEPARATOR
            . implode(Magento_Filesystem::DIRECTORY_SEPARATOR, array('var', 'theme'));

        $this->_request = $this->getMock('Zend_Controller_Request_Http', array('getParam'), array(), '', false);
        $this->_filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);
        $this->_session = $this->getMock('Mage_Backend_Model_Session', array(), array(), '', false);
        $this->_themeFactory = $this->getMock('Mage_Core_Model_Theme_FlyweightFactory', array('create'), array(),
            '', false);

        $this->_storageHelper = $this->getMock('Mage_Theme_Helper_Storage',
            array('_getRequest', 'urlDecode'), array(), '', false
        );
        $this->_storageHelper->expects($this->any())
            ->method('_getRequest')
            ->will($this->returnValue($this->_request));
        $this->_storageHelper->expects($this->any())
            ->method('urlDecode')
            ->will($this->returnArgument(0));

        $filesystemProperty = new ReflectionProperty($this->_storageHelper, '_filesystem');
        $filesystemProperty->setAccessible(true);
        $filesystemProperty->setValue($this->_storageHelper, $this->_filesystem);

        $sessionProperty = new ReflectionProperty($this->_storageHelper, '_session');
        $sessionProperty->setAccessible(true);
        $sessionProperty->setValue($this->_storageHelper, $this->_session);

        $themeFactoryProperty = new ReflectionProperty($this->_storageHelper, '_themeFactory');
        $themeFactoryProperty->setAccessible(true);
        $themeFactoryProperty->setValue($this->_storageHelper, $this->_themeFactory);
    }

    public function tearDown()
    {
        $this->_filesystem = null;
        $this->_session = null;
        $this->_themeFactory = null;
        $this->_request = null;
        $this->_storageHelper = null;
        $this->_customizationPath = null;
    }

    /**
     * @param $path
     */
    protected function _mockStorageRoot($path)
    {
        $storageRootProperty = new ReflectionProperty($this->_storageHelper, '_storageRoot');
        $storageRootProperty->setAccessible(true);
        $storageRootProperty->setValue($this->_storageHelper, $path);
    }

    /**
     * @param $path
     */
    protected function _mockCurrentPath($path)
    {
        $currentPathProperty = new ReflectionProperty($this->_storageHelper, '_currentPath');
        $currentPathProperty->setAccessible(true);
        $currentPathProperty->setValue($this->_storageHelper, $path);
    }

    /**
     * @covers Mage_Theme_Helper_Storage::getShortFilename
     */
    public function testGetShortFilename()
    {
        $longFileName     = 'veryLongFileNameMoreThanTwenty';
        $expectedFileName = 'veryLongFileNameMore...';
        $this->assertEquals($expectedFileName, $this->_storageHelper->getShortFilename($longFileName, 20));
    }

    /**
     * @covers Mage_Theme_Helper_Storage::getStorageRoot
     * @covers Mage_Theme_Helper_Storage::_getTheme
     * @covers Mage_Theme_Helper_Storage::getStorageType
     */
    public function testGetStorageRoot()
    {
        $themeId = 6;
        $requestMap = array(
            array(Mage_Theme_Helper_Storage::PARAM_THEME_ID, null, $themeId),
            array(Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE, null, Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE)
        );
        $this->_request->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($requestMap));

        $themeModel = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false);
        $this->_themeFactory->expects($this->any())->method('create')->will($this->returnValue($themeModel));
        $themeModel->expects($this->any())->method('getId')->will($this->returnValue($themeId));
        $customization = $this->getMock('Mage_Core_Model_Theme_Customization', array(), array(), '', false);
        $themeModel->expects($this->any())->method('getCustomization')->will($this->returnValue($customization));
        $customization->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($this->_customizationPath));

        $expectedStorageRoot = implode(Magento_Filesystem::DIRECTORY_SEPARATOR, array(
            $this->_customizationPath,
            Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE
        ));
        $this->assertEquals($expectedStorageRoot, $this->_storageHelper->getStorageRoot());
    }

    /**
     * @covers Mage_Theme_Helper_Storage::getThumbnailDirectory
     */
    public function testGetThumbnailDirectory()
    {
        $imagePath = implode(Magento_Filesystem::DIRECTORY_SEPARATOR, array('root', 'image', 'image_name.jpg'));
        $thumbnailDir = implode(
            Magento_Filesystem::DIRECTORY_SEPARATOR,
            array('root', 'image', Mage_Theme_Model_Wysiwyg_Storage::THUMBNAIL_DIRECTORY)
        );

        $this->assertEquals($thumbnailDir, $this->_storageHelper->getThumbnailDirectory($imagePath));
    }

    /**
     * @covers Mage_Theme_Helper_Storage::getThumbnailPath
     */
    public function testGetThumbnailPath()
    {
        $image       = 'image_name.jpg';
        $storageRoot = $this->_customizationPath . Magento_Filesystem::DIRECTORY_SEPARATOR
            . Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE;
        $currentPath = $storageRoot . Magento_Filesystem::DIRECTORY_SEPARATOR . 'some_dir';

        $imagePath   = $currentPath . Magento_Filesystem::DIRECTORY_SEPARATOR . $image;
        $thumbnailPath = implode(
            Magento_Filesystem::DIRECTORY_SEPARATOR,
            array($currentPath, Mage_Theme_Model_Wysiwyg_Storage::THUMBNAIL_DIRECTORY, $image)
        );

        $this->_filesystem->expects($this->atLeastOnce())
            ->method('has')
            ->with($imagePath)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->atLeastOnce())
            ->method('isPathInDirectory')
            ->with($imagePath, $storageRoot)
            ->will($this->returnValue(true));

        $this->_mockStorageRoot($storageRoot);
        $this->_mockCurrentPath($currentPath);

        $this->assertEquals($thumbnailPath, $this->_storageHelper->getThumbnailPath($image));
    }

    /**
     * @covers Mage_Theme_Helper_Storage::getRequestParams
     */
    public function testGetRequestParams()
    {
        $node = 'node';
        $themeId = 16;
        $contentType = Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE;

        $requestMap = array(
            array(Mage_Theme_Helper_Storage::PARAM_NODE, null, $node),
            array(Mage_Theme_Helper_Storage::PARAM_THEME_ID, null, $themeId),
            array(Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE, null, $contentType)
        );
        $this->_request->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($requestMap));

        $expectedResult = array(
            Mage_Theme_Helper_Storage::PARAM_THEME_ID     => $themeId,
            Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE => $contentType,
            Mage_Theme_Helper_Storage::PARAM_NODE         => $node
        );
        $this->assertEquals($expectedResult, $this->_storageHelper->getRequestParams());
    }

    /**
     * @covers Mage_Theme_Helper_Storage::getAllowedExtensionsByType
     */
    public function testGetAllowedExtensionsByType()
    {
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with(Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE)
            ->will($this->returnValue(Mage_Theme_Model_Wysiwyg_Storage::TYPE_FONT));

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with(Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE)
            ->will($this->returnValue(Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE));


        $fontTypes = $this->_storageHelper->getAllowedExtensionsByType();
        $this->assertEquals(array('ttf', 'otf', 'eot', 'svg', 'woff'), $fontTypes);

        $imagesTypes = $this->_storageHelper->getAllowedExtensionsByType();
        $this->assertEquals(array('jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'), $imagesTypes);
    }
}
