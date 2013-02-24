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
 * Test class for Mage_Core_Model_Config_Loader_Local
 */
class Mage_Core_Model_Config_Loader_LocalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Loader_Local
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_protFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customConfig;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customFile;

    protected function setUp()
    {
        $this->_customConfig = null;
        $this->_customFile = null;
        $this->_dirsMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false, false);
        $this->_protFactoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory',
            array(), array(), '', false, false);
        $this->_baseConfigMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
    }

    protected function createModel()
    {
        return new Mage_Core_Model_Config_Loader_Local(
            $this->_protFactoryMock,
            $this->_dirsMock,
            $this->_customConfig,
            $this->_customFile
        );
    }

    protected function tearDown()
    {
        unset($this->_protFactoryMock);
        unset($this->_dirsMock);
        unset($this->_baseConfigMock);
        unset($this->_model);
    }

    public function testLoadWithoutData()
    {
        $this->_dirsMock->expects($this->once())
            ->method('getDir')
            ->with($this->equalTo(Mage_Core_Model_Dir::CONFIG))
            ->will($this->returnValue('testdir\etc'));
        $this->_protFactoryMock->expects($this->never())
            ->method('create');
        $this->_baseConfigMock->expects($this->never())
            ->method('loadFile');
        $this->_baseConfigMock->expects($this->never())
            ->method('loadString');
        $this->_baseConfigMock->expects($this->never())
            ->method('extend');
        $this->createModel()->load($this->_baseConfigMock);
    }

    public function testLoadWithLocalConfig()
    {
        $localConfigFile = realpath(__DIR__. '/../_files/testdir/etc/local.xml');
        $this->_dirsMock->expects($this->once())
            ->method('getDir')
            ->with($this->equalTo(Mage_Core_Model_Dir::CONFIG))
            ->will($this->returnValue(realpath(__DIR__. '/../_files/testdir/etc')));
        $this->_protFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->with('<config/>')
            ->will($this->returnValue($this->_baseConfigMock));
        $this->_baseConfigMock->expects($this->once())
            ->method('loadFile')
            ->with($this->equalTo($localConfigFile))
            ->will($this->returnValue(true));
        $this->_baseConfigMock->expects($this->exactly(1))
            ->method('extend')
            ->with($this->equalTo($this->_baseConfigMock))
            ->will($this->returnValue($this->getMockBuilder('Varien_Simplexml_Config')
            ->disableOriginalConstructor()->getMock())
        );
        $this->createModel()->load($this->_baseConfigMock);
    }

    public function testLoadWithCustomConfig()
    {
        $localConfigFile = realpath(__DIR__. '/../_files/testdir/etc/local.xml');
        $this->_customFile = 'directorytest' . DS . 'testconfig.xml';
        $localConfigExtraFile = realpath(__DIR__. '/../_files/testdir/etc/directorytest/testconfig.xml');
        $this->_dirsMock->expects($this->once())
            ->method('getDir')
            ->with($this->equalTo(Mage_Core_Model_Dir::CONFIG))
            ->will($this->returnValue(realpath(__DIR__. '/../_files/testdir/etc/')));
        $this->_protFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with('<config/>')
            ->will($this->returnValue($this->_baseConfigMock));
        $this->_baseConfigMock->expects($this->at(0))
            ->method('loadFile')
            ->with($this->equalTo($localConfigFile))
            ->will($this->returnValue(true));
        $this->_baseConfigMock->expects($this->at(1))
            ->method('loadFile')
            ->with($this->equalTo($localConfigExtraFile))
            ->will($this->returnValue(true));
        $this->_baseConfigMock->expects($this->exactly(2))
            ->method('extend')
            ->with($this->equalTo($this->_baseConfigMock))
            ->will($this->returnValue($this->getMockBuilder('Varien_Simplexml_Config')
                ->disableOriginalConstructor()->getMock())
        );
        $this->createModel()->load($this->_baseConfigMock);
    }

    public function testLoadWithExtraLocalConfig()
    {
        $this->_customConfig = realpath(__DIR__. '/../_files/testdir/etc/testdirectory/customconfig.xml');
        $this->_dirsMock->expects($this->once())
            ->method('getDir')
            ->with($this->equalTo(Mage_Core_Model_Dir::CONFIG))
            ->will($this->returnValue(realpath(__DIR__. '/../_files/testdir/etc/testdirectory')));
        $this->_protFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->with('<config/>')
            ->will($this->returnValue($this->_baseConfigMock));
        $this->_baseConfigMock->expects($this->never())
            ->method('loadFile');
        $this->_baseConfigMock->expects($this->exactly(1))
            ->method('loadString')
            ->with($this->equalTo($this->_customConfig))
            ->will($this->returnValue(true));
        $this->_baseConfigMock->expects($this->exactly(1))
            ->method('extend')
            ->with($this->equalTo($this->_baseConfigMock))
            ->will($this->returnValue($this->getMockBuilder('Varien_Simplexml_Config')
                ->disableOriginalConstructor()->getMock())
        );
        $this->createModel()->load($this->_baseConfigMock);
    }
}