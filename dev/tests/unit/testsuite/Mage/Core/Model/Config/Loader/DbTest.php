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
 * Test class for Mage_Core_Model_Config_Loader_Db
 */
class Mage_Core_Model_Config_Loader_DbTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Loader_Db
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dbUpdaterMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modulesConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    protected function setUp()
    {
        $this->_modulesConfigMock = $this->getMock('Mage_Core_Model_Config_Modules',
            array(), array(), '', false, false
        );
        $this->_dbUpdaterMock = $this->getMock('Mage_Core_Model_Db_UpdaterInterface',
            array(), array(), '', false, false
        );
        $this->_resourceMock = $this->getMock('Mage_Core_Model_Resource_Config', array(), array(), '', false, false);
        $this->_factoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory', array(), array(), '', false, false);

        $this->_model = new Mage_Core_Model_Config_Loader_Db(
            $this->_modulesConfigMock,
            $this->_resourceMock,
            $this->_dbUpdaterMock,
            $this->_factoryMock
        );
    }

    protected function tearDown()
    {
        unset($this->_dbUpdaterMock);
        unset($this->_modulesConfigMock);
        unset($this->_resourceMock);
        unset($this->_factoryMock);
        unset($this->_model);
    }

    public function testLoadWithReadConnection()
    {
        $this->_resourceMock->expects($this->once())->method('getReadConnection')->will($this->returnValue(true));
        $this->_dbUpdaterMock->expects($this->once())->method('updateScheme');

        $configData = new Varien_Simplexml_Config();
        $configMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $this->_modulesConfigMock->expects($this->once())->method('getNode')->will($this->returnValue('config_node'));
        $this->_factoryMock->expects($this->once())->method('create')
            ->with('config_node')
            ->will($this->returnValue($configData));

        $configMock->expects($this->once())->method('extend')->with($configData);

        $this->_resourceMock->expects($this->once())->method('loadToXml')->with($configMock);

        $this->_model->load($configMock);
    }

    public function testLoadWithoutReadConnection()
    {
        $this->_resourceMock->expects($this->once())->method('getReadConnection')->will($this->returnValue(false));
        $this->_dbUpdaterMock->expects($this->never())->method('updateScheme');

        $configMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $configMock->expects($this->never())->method('extend');
        $this->_resourceMock->expects($this->never())->method('loadToXml');

        $this->_model->load($configMock);
    }
}
