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
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Config_Modules_Reader
 */
class Mage_Core_Model_Config_Modules_ReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileReaderMock;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Mage_Core_Model_Config_Modules', array(), array(), '', false, false);
        $this->_fileReaderMock = $this->getMock('Mage_Core_Model_Config_Loader_Modules_File',
            array(), array(), '', false, false);
        $this->_model = new Mage_Core_Model_Config_Modules_Reader(
            $this->_configMock,
            $this->_fileReaderMock
        );
    }

    protected function tearDown()
    {
        unset($this->_configMock);
        unset($this->_fileReaderMock);
        unset($this->_model);
    }

    public function testLoadModulesConfiguration()
    {
        $fileName = 'acl.xml';
        $mergeToObjectMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $mergeModelMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $this->_fileReaderMock->expects($this->once())
            ->method('loadConfigurationFromFile')
            ->with($this->equalTo($this->_configMock),
                   $this->equalTo($fileName),
                   $this->equalTo($mergeToObjectMock),
                   $this->equalTo($mergeModelMock))
            ->will($this->returnValue('test_data')
        );
        $result = $this->_model->loadModulesConfiguration($fileName, $mergeToObjectMock, $mergeModelMock);
        $this->assertEquals('test_data', $result);
    }

    public function testGetModuleConfigurationFiles()
    {
        $fileName = 'acl.xml';
        $this->_fileReaderMock->expects($this->once())
            ->method('getConfigurationFiles')
            ->with($this->equalTo($this->_configMock),
                   $this->equalTo($fileName))
            ->will($this->returnValue('test_data')
        );
        $result = $this->_model->getModuleConfigurationFiles($fileName);
        $this->assertEquals('test_data', $result);
    }

    public function testGetModuleDir()
    {
        $type = 'some_type';
        $moduleName = 'some_module';
        $this->_fileReaderMock->expects($this->once())
            ->method('getModuleDir')
            ->with($this->equalTo($type),
                   $this->equalTo($moduleName))
            ->will($this->returnValue('test_data')
        );
        $result = $this->_model->getModuleDir($type, $moduleName);
        $this->assertEquals('test_data', $result);
    }
}