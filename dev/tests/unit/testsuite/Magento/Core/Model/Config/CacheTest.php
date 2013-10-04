<?php
/**
 * Test class for \Magento\Core\Model\Config\Cache
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

namespace Magento\Core\Model\Config;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Cache
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    protected function setUp()
    {
        $this->_cacheMock = $this->getMock('Magento\Core\Model\Cache\Type\Config', array(), array(), '', false, false);
        $this->_baseFactoryMock = $this->getMock('Magento\Core\Model\Config\BaseFactory',
            array(), array(), '', false, false);
        $this->_model = new \Magento\Core\Model\Config\Cache($this->_cacheMock, $this->_baseFactoryMock);
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
            ->method('load')
            ->will($this->returnValue(array('sourceData' => 'test_config')));

        $this->_baseFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo(array('sourceData' => 'test_config')))
            ->will($this->returnValue('some_instance'));

        $this->assertEquals('some_instance', $this->_model->load());
    }

    public function testSave()
    {
        $configMock = $this->getMock('Magento\Core\Model\Config\Base', array(), array(), '', false);
        $nodeMock = $this->getMock('stdClass', array('asNiceXml'));

        $configMock->expects($this->once())->method('getNode')->will($this->returnValue($nodeMock));
        $nodeMock->expects($this->once())->method('asNiceXml')->with('', false)->will($this->returnValue('test'));
        $this->_cacheMock->expects($this->once())->method('save')->with('test');
        $this->_model->save($configMock);
    }

    public function testClean()
    {
        $this->_cacheMock->expects($this->once())
            ->method('clean');
        $this->_model->clean();
    }
}
