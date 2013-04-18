<?php
/**
 * Test class for Mage_Core_Model_Config_Loader
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Config_LoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Loader
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modulesConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_localesConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dbLoaderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseFactoryMock;

    protected function setUp()
    {
        $this->_modulesConfigMock = $this->getMock('Mage_Core_Model_Config_Modules',
            array('getNode'), array(), '', false, false);
        $this->_localesConfigMock = $this->getMock('Mage_Core_Model_Config_Locales',
            array(), array(), '', false, false);
        $this->_baseConfigMock = $this->getMock('Mage_Core_Model_Config_Base',
            array('extend'), array(), '', false, false);
        $this->_dbLoaderMock = $this->getMock('Mage_Core_Model_Config_Loader_Db', array(), array(), '', false, false);
        $this->_baseFactoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory',
            array('create'), array(), '', false, false);
        $this->_model = new Mage_Core_Model_Config_Loader(
            $this->_modulesConfigMock,
            $this->_localesConfigMock,
            $this->_dbLoaderMock,
            $this->_baseFactoryMock
        );
    }

    protected function tearDown()
    {
        unset($this->_modulesConfigMock);
        unset($this->_localesConfigMock);
        unset($this->_dbLoaderMock);
        unset($this->_baseConfigMock);
        unset($this->_baseFactoryMock);
        unset($this->_model);
    }

    public function testLoad()
    {
        $element = new Varien_Simplexml_Element('<config>test_data</config>');
        $elementConfig = new Varien_Simplexml_Config();
        $this->_modulesConfigMock->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($element));
        $this->_localesConfigMock->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($element));
        $this->_baseFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with($element)
            ->will($this->returnValue($elementConfig));
        $this->_baseConfigMock->expects($this->exactly(2))
            ->method('extend')
            ->with($this->equalTo($elementConfig))
            ->will($this->returnValue($element));
        $this->_model->load($this->_baseConfigMock);
    }
}
