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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme model
 */
class Mage_Core_Model_Theme_ImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Theme_Image
     */
    protected $_model;

    /**
     * @var Magento_ObjectManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager', get_class_methods('Magento_ObjectManager'),
            array(), '', false);
        $this->_helper = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);
        $this->_filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);
        $this->_model = new Mage_Core_Model_Theme_Image($this->_objectManager, $this->_helper, $this->_filesystem);
        $this->_model->setTheme($this->getMock('Mage_Core_Model_Theme', array(), array(), '', false));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Design_Package
     */
    protected function _getDesignMock()
    {
        $designMock = $this->getMock('Mage_Core_Model_Design_Package', array('getViewFileUrl', 'getPublicDir'),
            array(), '', false);
        $designMock->expects($this->any())
            ->method('getPublicDir')
            ->will($this->returnValue('pub/media/theme'));
        $this->_objectManager->expects($this->any())
            ->method('get')
            ->with($this->equalTo('Mage_Core_Model_Design_Package'))
            ->will($this->returnValue($designMock));
        return $designMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Dir
     */
    protected function _getDirMock()
    {
        $dirMock = $this->getMock('Mage_Core_Model_Dir', array('getDir'), array(), '', false);
        $dirMock->expects($this->any())
            ->method('getDir')
            ->with($this->equalTo(Mage_Core_Model_Dir::MEDIA))
            ->will($this->returnValue('pub/media'));
        $this->_objectManager->expects($this->any())
            ->method('get')
            ->with($this->equalTo('Mage_Core_Model_Dir'))
            ->will($this->returnValue($dirMock));
        return $dirMock;
    }

    public function testSavePreviewImage()
    {
        $this->_model->setTheme($this->getMock('Mage_Core_Model_Theme', array(), array(), '', false));
        $this->assertInstanceOf('Mage_Core_Model_Theme_Image', $this->_model->savePreviewImage());
    }

    /**
     * @return string
     */
    public function testGetPreviewImageDirectoryUrl()
    {
        $store = $this->getMock('Mage_Core_Model_Store', array('getBaseUrl'), array(), '', false);
        $app = $this->getMock('Mage_Core_Model_App', array('getStore'), array(), '', false);
        $app->expects($this->once())->method('getStore')->will($this->returnValue($store));
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with('Mage_Core_Model_App')
            ->will($this->returnValue($app));

        $store->expects($this->once())->method('getBaseUrl')
            ->with(Mage_Core_Model_Store::URL_TYPE_MEDIA)->will($this->returnValue('http://example.com/media/'));
        $expectedValue = 'http://example.com/media/theme/preview/';
        $this->assertEquals($expectedValue, $this->_model->getPreviewImageDirectoryUrl());
        return $expectedValue;
    }

    public function testCreatePreviewImageCopy()
    {
        $fileName = $this->_getDirMock()->getDir(Mage_Core_Model_Dir::MEDIA)
            . DIRECTORY_SEPARATOR . 'theme'
            . DIRECTORY_SEPARATOR . 'preview'
            . DIRECTORY_SEPARATOR . 'image.jpg';

        $this->_filesystem->expects($this->any())
            ->method('copy')
            ->with($this->equalTo($fileName), $this->equalTo($fileName))
            ->will($this->returnValue(true));

        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('getPreviewImage'), array(), '', false);
        $themeMock->expects($this->any())
            ->method('getPreviewImage')
            ->will($this->returnValue('image.jpg'));

        $this->_model->setTheme($themeMock);

        $this->assertInstanceOf('Mage_Core_Model_Theme_Image', $this->_model->createPreviewImageCopy());
        $this->assertEquals('image.jpg', $this->_model->getPreviewImage());
    }

    /**
     * @depends testGetPreviewImageDirectoryUrl
     */
    public function testGetPreviewImageUrl($previewDirUrl)
    {
        /** @var $model PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Theme_Image */
        $model = $this->getMock(
            'Mage_Core_Model_Theme_Image', array('getPreviewImageDirectoryUrl'), array(), '', false
        );
        $model->expects($this->once())->method('getPreviewImageDirectoryUrl')->will($this->returnValue($previewDirUrl));
        $theme = $this->getMock('Mage_Core_Model_Theme', array('getPreviewImage'), array(), '', false);
        $theme->expects($this->exactly(2))->method('getPreviewImage')->will($this->returnValue('images/logo.gif'));
        $model->setTheme($theme);
        $this->assertEquals('http://example.com/media/theme/preview/images/logo.gif', $model->getPreviewImageUrl());
    }

    public function testGetPreviewImageUrlDefault()
    {
        $theme = $this->getMock('Mage_Core_Model_Theme', array('getPreviewImage'), array(), '', false);
        $theme->expects($this->once())->method('getPreviewImage')->will($this->returnValue(false));
        $expectedValue = 'http://example.com/pub/static/_module/Mage_Core/theme/default_preview.jpg';
        $designPackage = $this->_getDesignMock();
        $designPackage->expects($this->once())->method('getViewFileUrl')->with('Mage_Core::theme/default_preview.jpg')
            ->will($this->returnValue($expectedValue));
        $this->_model->setTheme($theme);
        $this->assertEquals($expectedValue, $this->_model->getPreviewImageUrl());
    }
}
