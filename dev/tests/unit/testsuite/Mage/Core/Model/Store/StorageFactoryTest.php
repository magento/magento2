<?php
/**
 * Test class for Mage_Core_Model_Store_StorageFactory
 *
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

class Mage_Core_Model_Store_StorageFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Store_StorageFactory
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var string
     */
    protected $_defaultStorage = 'Mage_Core_Model_Store_Storage_Default';

    /**
     * @var string
     */
    protected $_dbStorage = 'Mage_Core_Model_Store_Storage_Db';

    /**
     * @var array
     */
    protected $_arguments = array();

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storage;

    protected function setUp()
    {
        $this->_arguments = array('test' => 'argument');
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager');
        $this->_eventManagerMock = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        $this->_logMock = $this->getMock('Mage_Core_Model_Logger', array(), array(), '', false);
        $this->_configMock = $this->getMock('Mage_Core_Model_ConfigInterface', array(), array(), '', false);
        $this->_appMock = $this->getMock('Mage_Core_Model_App_Proxy', array(), array(), '', false);
        $this->_appStateMock = $this->getMock('Mage_Core_Model_App_State', array(), array(), '', false);
        $this->_storage = $this->getMock('Mage_Core_Model_Store_StorageInterface');

        $this->_model = new Mage_Core_Model_Store_StorageFactory(
            $this->_objectManagerMock,
            $this->_eventManagerMock,
            $this->_logMock,
            $this->_configMock,
            $this->_appMock,
            $this->_appStateMock,
            $this->_defaultStorage,
            $this->_dbStorage
        );
    }

    public function testGetInNotInstalledModeWithInternalCache()
    {
        $this->_appStateMock->expects($this->exactly(2))->method('isInstalled')->will($this->returnValue(false));

        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($this->_defaultStorage)
            ->will($this->returnValue($this->_storage));

        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_logMock->expects($this->never())->method('initForStore');
        $this->_appMock->expects($this->never())->method('setUseSessionInUrl');

        /** test create instance */
        $this->assertEquals($this->_storage, $this->_model->get($this->_arguments));

        /** test read instance from internal cache */
        $this->assertEquals($this->_storage, $this->_model->get($this->_arguments));
    }

    public function testGetInstalledModeWithInternalCache()
    {
        $this->_appStateMock->expects($this->exactly(2))->method('isInstalled')->will($this->returnValue(true));

        $store = $this->getMock('Mage_Core_Model_Store', array(), array(), '', false);

        $this->_storage
            ->expects($this->exactly(2))
            ->method('getStore')
            ->will($this->returnValue($store));

        $store->expects($this->once())
            ->method('getConfig')
            ->with(Mage_Core_Model_Session_Abstract::XML_PATH_USE_FRONTEND_SID)
            ->will($this->returnValue(true));


        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($this->_dbStorage)
            ->will($this->returnValue($this->_storage));

        $this->_eventManagerMock->expects($this->once())->method('dispatch')->with('core_app_init_current_store_after');
        $this->_logMock
            ->expects($this->once())
            ->method('initForStore')
            ->with($store, $this->_configMock);

        $this->_appMock->expects($this->once())->method('setUseSessionInUrl')->with(true);

        /** test create instance */
        $this->assertEquals($this->_storage, $this->_model->get($this->_arguments));

        /** test read instance from internal cache */
        $this->assertEquals($this->_storage, $this->_model->get($this->_arguments));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetWithInvalidStorageClassName()
    {
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));

        $invalidObject = $this->getMock('Mage_Core_Model_Store', array(), array(), '', false);

        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($this->_dbStorage)
            ->will($this->returnValue($invalidObject));

        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_logMock->expects($this->never())->method('initForStore');
        $this->_appMock->expects($this->never())->method('setUseSessionInUrl');

        /** test create instance */
        $this->assertEquals($this->_storage, $this->_model->get($this->_arguments));

        /** test read instance from internal cache */
        $this->assertEquals($this->_storage, $this->_model->get($this->_arguments));
    }
}
