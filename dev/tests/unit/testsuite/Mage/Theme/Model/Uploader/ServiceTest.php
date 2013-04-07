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
class Mage_Theme_Model_Uploader_ServiceTest extends PHPUnit_Framework_TestCase
{
    public function testGetCssUploadMaxSize()
    {
        /** @var $service Mage_Theme_Model_Uploader_Service */
        $service = $this->getMock('Mage_Theme_Model_Uploader_Service', array('_getMaxUploadSize'), array(), '', false);

        $service->expects($this->once())
            ->method('_getMaxUploadSize')
            ->with(Mage_Theme_Model_Uploader_Service::XML_PATH_CSS_UPLOAD_LIMIT)
            ->will($this->returnValue('5M'));

        $this->assertEquals('5M', $service->getCssUploadMaxSize());
    }

    public function testGetJsUploadMaxSize()
    {
        /** @var $service Mage_Theme_Model_Uploader_Service */
        $service = $this->getMock('Mage_Theme_Model_Uploader_Service', array('_getMaxUploadSize'), array(), '', false);

        $service->expects($this->once())
            ->method('_getMaxUploadSize')
            ->with(Mage_Theme_Model_Uploader_Service::XML_PATH_JS_UPLOAD_LIMIT)
            ->will($this->returnValue('3M'));

        $this->assertEquals('3M', $service->getJsUploadMaxSize());
    }

    public function testGetFileContent()
    {
        $fileName = 'file.name';
        /** @var $filesystemMock Varien_Io_File */
        $filesystemMock = $this->getMock('Varien_Io_File', array('read'), array(), '', false, false);
        /** @var $service Mage_Theme_Model_Uploader_Service */
        $service = $this->getMock('Mage_Theme_Model_Uploader_Service', array('getFilePath'), array(), '', false);

        $service->expects($this->once())
            ->method('getFilePath')
            ->will($this->returnValue($fileName));

        $filesystemMock->expects($this->once())
            ->method('read')
            ->with($fileName)
            ->will($this->returnValue('content from my file'));

        $property = new ReflectionProperty($service, '_fileIo');
        $property->setAccessible(true);
        $property->setValue($service, $filesystemMock);

        $this->assertEquals('content from my file', $service->getFileContent());
    }

    public function testUploadCssFile()
    {
        $file['tmp_name'] = 'file.name';
        /** @var $service Mage_Theme_Model_Uploader_Service */
        $service = $this->getMock('Mage_Theme_Model_Uploader_Service', array('_getMaxUploadSize'), array(), '', false);

        $service->expects($this->once())
            ->method('_getMaxUploadSize')
            ->with(Mage_Theme_Model_Uploader_Service::XML_PATH_CSS_UPLOAD_LIMIT)
            ->will($this->returnValue('5'));

        $objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);
        $uploader = $this->getMock('Mage_Core_Model_File_Uploader', array(), array(), '', false);

        $uploader->expects($this->once())
            ->method('getFileSize')
            ->will($this->returnValue('3'));

        $uploader->expects($this->once())
            ->method('validateFile')
            ->will($this->returnValue($file));

        $objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($uploader));

        $property = new ReflectionProperty($service, '_objectManager');
        $property->setAccessible(true);
        $property->setValue($service, $objectManager);

        $this->assertEquals('file.name', $service->uploadCssFile($file)->getFilePath());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testUploadInvalidCssFile()
    {
        $file['tmp_name'] = 'file.name';
        /** @var $service Mage_Theme_Model_Uploader_Service */
        $service = $this->getMock(
            'Mage_Theme_Model_Uploader_Service', array('_getMaxUploadSize', 'getCssUploadMaxSizeInMb'),
            array(), '', false
        );

        $dataHelper = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);

        $service->expects($this->once())
            ->method('_getMaxUploadSize')
            ->with(Mage_Theme_Model_Uploader_Service::XML_PATH_CSS_UPLOAD_LIMIT)
            ->will($this->returnValue('10'));

        $service->expects($this->once())
            ->method('getCssUploadMaxSizeInMb')
            ->will($this->returnValue('10'));

        $objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);
        $uploader = $this->getMock('Mage_Core_Model_File_Uploader', array(), array(), '', false);

        $uploader->expects($this->once())
            ->method('getFileSize')
            ->will($this->returnValue('30'));

        $objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($uploader));

        $objectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($dataHelper));

        $property = new ReflectionProperty($service, '_objectManager');
        $property->setAccessible(true);
        $property->setValue($service, $objectManager);

        $this->assertEquals('file.name', $service->uploadCssFile($file)->getFilePath());
    }

    public function testUploadJsFile()
    {
        $file['tmp_name'] = 'file.name';

        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false, false);

        /** @var $service Mage_Theme_Model_Uploader_Service */
        $service = $this->getMock(
            'Mage_Theme_Model_Uploader_Service', array('_getMaxUploadSize', 'getFileContent'), array(), '', false
        );

        $fileJs = $this->getMock(
            'Mage_Core_Model_Theme_Customization_Files_Js', array(), array(), '', false
        );

        $fileJs->expects($this->once())
            ->method('saveJsFile')
            ->will($this->returnValue($file));

        $service->expects($this->once())
            ->method('_getMaxUploadSize')
            ->with(Mage_Theme_Model_Uploader_Service::XML_PATH_JS_UPLOAD_LIMIT)
            ->will($this->returnValue('500'));

        $service->expects($this->once())
            ->method('getFileContent')
            ->will($this->returnValue('Uploaded file content'));

        $objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);
        $uploader = $this->getMock('Mage_Core_Model_File_Uploader', array(), array(), '', false);

        $uploader->expects($this->once())
            ->method('getFileSize')
            ->will($this->returnValue('499'));

        $uploader->expects($this->once())
            ->method('validateFile')
            ->will($this->returnValue($file));

        $objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($uploader));

        $property = new ReflectionProperty($service, '_objectManager');
        $property->setAccessible(true);
        $property->setValue($service, $objectManager);

        $property = new ReflectionProperty($service, '_filesJs');
        $property->setAccessible(true);
        $property->setValue($service, $fileJs);

        $this->assertEquals('file.name', $service->uploadJsFile($file, $theme)->getFilePath());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testUploadInvalidJsFile()
    {
        $file['tmp_name'] = 'file.name';

        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false);

        $dataHelper = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);

        /** @var $service Mage_Theme_Model_Uploader_Service */
        $service = $this->getMock(
            'Mage_Theme_Model_Uploader_Service', array('_getMaxUploadSize', 'getFileContent', 'getJsUploadMaxSizeInMb'),
            array(), '', false
        );

        $fileJs = $this->getMock(
            'Mage_Core_Model_Theme_Customization_Files_Js', array(), array(), '', false
        );

        $service->expects($this->once())
            ->method('_getMaxUploadSize')
            ->with(Mage_Theme_Model_Uploader_Service::XML_PATH_JS_UPLOAD_LIMIT)
            ->will($this->returnValue('100'));

        $service->expects($this->once())
            ->method('getJsUploadMaxSizeInMb')
            ->will($this->returnValue('499'));

        $objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);
        $uploader = $this->getMock('Mage_Core_Model_File_Uploader', array(), array(), '', false);

        $uploader->expects($this->once())
            ->method('getFileSize')
            ->will($this->returnValue('499'));

        $objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($uploader));

        $objectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($dataHelper));

        $property = new ReflectionProperty($service, '_objectManager');
        $property->setAccessible(true);
        $property->setValue($service, $objectManager);

        $property = new ReflectionProperty($service, '_filesJs');
        $property->setAccessible(true);
        $property->setValue($service, $fileJs);

        $this->assertEquals('file.name', $service->uploadJsFile($file, $theme)->getFilePath());
    }
}
