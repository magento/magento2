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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Helper_Data
     */
    protected $_helper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    public function setUp()
    {
        $this->_configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false, false);
        $this->_helper = new Mage_Backend_Helper_Data($this->_configMock,
            $this->getMock('Mage_Core_Helper_Context', array(), array(), '', false, false)
        );
    }

    public function testGetAreaFrontNameReturnsDefaultValueWhenCustomNotSet()
    {
        $this->_configMock->expects($this->at(0))->method('getNode')
            ->with(Mage_Backend_Helper_Data::XML_PATH_USE_CUSTOM_ADMIN_PATH)
            ->will($this->returnValue(false));

        $this->_configMock->expects($this->at(1))->method('getNode')
            ->with(Mage_Backend_Helper_Data::XML_PATH_BACKEND_FRONTNAME)
            ->will($this->returnValue('backend'));

        $this->assertEquals('backend', $this->_helper->getAreaFrontName());
    }

    public function testGetAreaFrontNameReturnsDefaultValueWhenCustomIsSet()
    {
        $this->_configMock->expects($this->at(0))->method('getNode')
            ->with(Mage_Backend_Helper_Data::XML_PATH_USE_CUSTOM_ADMIN_PATH)
            ->will($this->returnValue(true));

        $this->_configMock->expects($this->at(1))->method('getNode')
            ->with(Mage_Backend_Helper_Data::XML_PATH_CUSTOM_ADMIN_PATH)
            ->will($this->returnValue('control'));

        $this->assertEquals('control', $this->_helper->getAreaFrontName());
    }

    public function testGetAreaFrontNameReturnsEmptyStringIfAreaFrontNameDoesntExist()
    {
        $this->_configMock->expects($this->at(0))->method('getNode')
            ->with(Mage_Backend_Helper_Data::XML_PATH_USE_CUSTOM_ADMIN_PATH)
            ->will($this->returnValue(false));

        $this->_configMock->expects($this->at(1))->method('getNode')
            ->with(Mage_Backend_Helper_Data::XML_PATH_BACKEND_FRONTNAME)
            ->will($this->returnValue(null));


        $this->assertNotNull($this->_helper->getAreaFrontName());
        $this->assertEmpty($this->_helper->getAreaFrontName());
    }

    public function testClearAreaFrontName()
    {
        $this->_configMock->expects($this->exactly(4))->method('getNode');
        $this->_helper->getAreaFrontName();
        $this->_helper->clearAreaFrontName();
        $this->_helper->getAreaFrontName();
    }

    public function testGetAreaFrontNameReturnsValueFromCache()
    {
        $this->_configMock->expects($this->exactly(2))->method('getNode');
        $this->_helper->getAreaFrontName();
        $this->_helper->getAreaFrontName();
    }
}
