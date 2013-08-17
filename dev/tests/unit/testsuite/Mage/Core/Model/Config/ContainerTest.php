<?php
/**
 * Test class for Mage_Core_Model_Config_Container
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

class Mage_Core_Model_Config_ContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Container
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configSectionsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configCacheMock;

    protected function setUp()
    {
        $this->_configSectionsMock = $this->getMock('Mage_Core_Model_Config_Sections', array(), array(), '', false);
        $this->_configCacheMock = $this->getMock('Mage_Core_Model_Config_Cache', array(), array(), '', false);
        $factoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory', array('create'), array(), '', false);
        $this->_dataMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false);
        $factoryMock->expects($this->once())->method('create')->will($this->returnValue($this->_dataMock));

        $arguments = array(
            'sections' => $this->_configSectionsMock,
            'factory' => $factoryMock,
            'configCache' => $this->_configCacheMock,
        );
        $helper = new Magento_Test_Helper_ObjectManager($this);
        $this->_model = $helper->getObject('Mage_Core_Model_Config_Container', $arguments);
    }

    public function testGetNodeWithoutSectionKey()
    {
        $path = 'global/cache';
        $this->_configSectionsMock
            ->expects($this->once())
            ->method('getKey')
            ->with($path)
            ->will($this->returnValue(false));

        $this->_dataMock->expects($this->once())
            ->method('getNode')
            ->with($path)
            ->will($this->returnValue('expectedValue'));

        $this->assertEquals('expectedValue', $this->_model->getNode($path));
    }

    public function testGetNodeWhenCacheSectionIsNotLoadedAndConfigValue()
    {
        $path = 'global/cache';
        $sectionKey = 'global';
        $expected = 'expectedValue';

        $sectionMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false);

        $this->_configSectionsMock
            ->expects($this->once())
            ->method('getKey')
            ->with($path)
            ->will($this->returnValue($sectionKey));

        $this->_configCacheMock
            ->expects($this->once())
            ->method('getSection')
            ->with($sectionKey)
            ->will($this->returnValue($sectionMock));

        $sectionMock->expects($this->once())->method('getNode')->with('cache')->will($this->returnValue($expected));
        $this->_dataMock->expects($this->never())->method('getNode');
        $this->assertEquals($expected, $this->_model->getNode($path));
    }

    public function testGetNodeWhenCacheSectionIsNotLoadedAndWithoutConfigValue()
    {
        $path = 'global/cache';
        $sectionKey = 'global';
        $expected = 'expectedValue';

        $sectionMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false);

        $this->_configSectionsMock
            ->expects($this->once())
            ->method('getKey')
            ->with($path)
            ->will($this->returnValue($sectionKey));

        $this->_configCacheMock
            ->expects($this->once())
            ->method('getSection')
            ->with($sectionKey)
            ->will($this->returnValue($sectionMock));

        $sectionMock->expects($this->once())->method('getNode')->with('cache')->will($this->returnValue(false));
        $this->_dataMock->expects($this->once())->method('getNode')->with($path)->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->_model->getNode($path));
    }

    public function testGetNodeWithoutSection()
    {
        $path = 'global/cache';
        $sectionKey = 'global';
        $expected = 'expectedValue';

        $this->_configSectionsMock
            ->expects($this->once())
            ->method('getKey')
            ->with($path)
            ->will($this->returnValue($sectionKey));

        $this->_configCacheMock
            ->expects($this->once())
            ->method('getSection')
            ->with($sectionKey)
            ->will($this->returnValue(false));

        $this->_dataMock->expects($this->once())->method('getNode')->with($path)->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->_model->getNode($path));
    }

    public function testGetNodeWithPathNull()
    {
        $path = null;
        $this->_dataMock->expects($this->once())->method('getNode')->with($path)->will($this->returnValue($path));

        $this->assertNull($this->_model->getNode($path));
    }

    public function testSetNodeWithPath()
    {
        $path = 'global/cache';
        $sectionKey = 'global';
        $section = 'cache';

        $this->_configSectionsMock
            ->expects($this->once())
            ->method('getKey')
            ->with($path)
            ->will($this->returnValue($sectionKey));

        $sectionMock = $this->getMock('Varien_Simplexml_Config', array(), array(), '', false);

        $this->_configCacheMock
            ->expects($this->once())
            ->method('getSection')
            ->with($sectionKey)
            ->will($this->returnValue($sectionMock));

        $sectionMock->expects($this->once())->method('setNode')->with($section)->will($this->returnValue($sectionMock));
        $this->_dataMock->expects($this->once())
            ->method('setNode');

         $this->_model->setNode($path, 'value');
    }

    public function testSetNodeWithoutSection()
    {
        $path = 'global/cache';
        $sectionKey = 'global';

        $this->_configSectionsMock
            ->expects($this->once())
            ->method('getKey')
            ->with($path)
            ->will($this->returnValue($sectionKey));

        $this->_configCacheMock
            ->expects($this->once())
            ->method('getSection')
            ->with($sectionKey)
            ->will($this->returnValue(false));

        $this->_dataMock->expects($this->once())
            ->method('setNode')
            ->with($path);

        $this->_model->setNode($path, 'value');
    }

    public function testSetNodeWithPathNull()
    {
        $path = null;

        $this->_dataMock->expects($this->once())
            ->method('setNode')
            ->with($path);

        $this->_model->setNode($path, 'value');
    }

    public function testGetXpath()
    {
        $xpath = 'someXpath';
        $expected = array();

        $this->_dataMock->expects($this->once())
            ->method('getXpath')
            ->with($xpath)
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->_model->getXpath($xpath));
    }
}