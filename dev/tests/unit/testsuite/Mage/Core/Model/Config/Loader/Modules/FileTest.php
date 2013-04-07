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
 * Test class for Mage_Core_Model_Config_Loader_Modules_File
 */
class Mage_Core_Model_Config_Loader_Modules_FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Loader_Modules_File
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modulesConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_protFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    protected function setUp()
    {
        $this->_modulesConfigMock = $this->getMock('Mage_Core_Model_Config_Modules',
            array(), array(), '', false, false);
        $this->_protFactoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory',
            array(), array(), '', false, false);
        $this->_dirsMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false, false);
        $this->_baseConfigMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $this->_model = new Mage_Core_Model_Config_Loader_Modules_File(
            $this->_dirsMock,
            $this->_protFactoryMock
        );
    }

    protected function tearDown()
    {
        unset($this->_modulesConfigMock);
        unset($this->_protFactoryMock);
        unset($this->_dirsMock);
        unset($this->_baseConfigMock);
        unset($this->_model);
    }

    public function testLoadConfigurationFromFile()
    {
        $nodes = new Mage_Core_Model_Config_Element('<modules><mod1><active>1</active></mod1></modules>');
        $fileName = 'acl.xml';
        $this->_protFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with($this->equalTo('<config/>'))
            ->will($this->returnValue($this->_baseConfigMock));

        $this->_modulesConfigMock->expects($this->once())
            ->method('getNode')
            ->with('modules')
            ->will($this->returnValue($nodes));

        $result = $this->_model->loadConfigurationFromFile($this->_modulesConfigMock, $fileName, null, null, array());
        $this->assertInstanceOf('Mage_Core_Model_Config_Base', $result);
    }

    public function testLoadConfigurationFromFileMergeToObject()
    {
        $nodes = new Mage_Core_Model_Config_Element('<config><mod1><active>1</active></mod1></config>');
        $modulesConfigMock = $this->getMock('Mage_Core_Model_ConfigInterface', array(), array(), '', false, false);
        $fileName = 'acl.xml';

        $mergeToObject = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $mergeModel = null;
        $configCache = array();
        $modulesConfigMock->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($nodes)
        );
        $this->_protFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('<config/>'))
            ->will($this->returnValue($mergeToObject));

        $this->_model->loadConfigurationFromFile($modulesConfigMock, $fileName, $mergeToObject, $mergeModel,
            $configCache);
    }

    public function testGetModuleDirWithData()
    {
        $moduleName = 'test';
        $type = 'etc';
        $path = realpath(__DIR__. '/../../_files/testdir/etc');
        $this->_model->setModuleDir($moduleName, $type, $path);
        $this->assertEquals($path, $this->_model->getModuleDir($type, $moduleName));
    }
}