<?php
/**
 * Test class for Mage_Core_Model_Config_Cache
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

class Mage_Core_Model_Config_CacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Cache
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configSectionsMock;

    protected function setUp()
    {
        $this->_cacheMock = $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false, false);
        $this->_configSectionsMock = $this->getMock('Mage_Core_Model_Config_Sections',
            array(), array(), '', false, false);
        $this->_contFactoryMock = $this->getMock('Mage_Core_Model_Config_ContainerFactory',
            array(), array(), '', false, false);
        $this->_baseFactoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory',
            array(), array(), '', false, false);
        $this->_model = new Mage_Core_Model_Config_Cache(
            $this->_cacheMock,
            $this->_configSectionsMock,
            $this->_contFactoryMock,
            $this->_baseFactoryMock
        );
    }

    protected function tearDown()
    {
        unset($this->_cacheMock);
        unset($this->_configSectionsMock);
        unset($this->_contFactoryMock);
        unset($this->_baseFactoryMock);
        unset($this->_model);
    }


    public function testCacheLifetime()
    {
        $lifetime = 10;
        $this->_model->setCacheLifetime($lifetime);
        $this->assertEquals($lifetime, $this->_model->getCacheLifeTime());
    }

    public function testLoadWithoutConfig()
    {
        $this->assertFalse($this->_model->load());
    }

    public function testLoadWithConfig()
    {
        $this->_cacheMock->expects($this->once())
            ->method('canUse')
            ->with('config')
            ->will($this->returnValue(true));
        $this->_cacheMock->expects($this->at(1))
            ->method('load')
            ->will($this->returnValue(false));
        $this->_cacheMock->expects($this->at(2))
            ->method('load')
            ->will($this->returnValue('test_config'));
        $this->_contFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo(array('sourceData' => 'test_config')))
            ->will($this->returnValue('some_instance'));

        $this->assertEquals('some_instance', $this->_model->load());
    }
}
