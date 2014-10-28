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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * \Magento\Framework\Cache\Core test case
 */
namespace Magento\Framework\Cache;

class CoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Cache\Core
     */
    protected $_core;

    /**
     * @var array
     */
    protected static $_cacheStorage = array();

    /**
     * Selected mock of \Zend_Cache_Backend_File to have extended
     * \Zend_Cache_Backend and implemented \Zend_Cache_Backend_Interface
     *
     * @var \Zend_Cache_Backend_File
     */
    protected $_mockBackend;

    protected function setUp()
    {
        $this->_mockBackend = $this->getMock('Zend_Cache_Backend_File');
    }

    protected function tearDown()
    {
        unset($this->_mockBackend);
    }

    public function testSetBackendDefault()
    {
        $core = new \Magento\Framework\Cache\Core();
        $core->setBackend($this->_mockBackend);

        $this->assertNotInstanceOf('Magento\Framework\Cache\Backend\Decorator\AbstractDecorator', $core->getBackend());
        $this->assertEquals($this->_mockBackend, $core->getBackend());
    }

    /**
     * @dataProvider setBackendExceptionProvider
     * @expectedException \Zend_Cache_Exception
     */
    public function testSetBackendException($decorators)
    {
        $core = new \Magento\Framework\Cache\Core(array('backend_decorators' => $decorators));
        $core->setBackend($this->_mockBackend);
    }

    public function setBackendExceptionProvider()
    {
        return array(
            'string' => array('string'),
            'decorator setting is not an array' => array(array('decorator' => 'string')),
            'decorator setting is empty array' => array(array('decorator' => array())),
            'no class index in array' => array(array('decorator' => array('somedata'))),
            'non-existing class passed' => array(array('decorator' => array('class' => 'NonExistingClass')))
        );
    }

    public function testSaveDisabled()
    {
        $backendMock = $this->getMock('Zend_Cache_Backend_BlackHole');
        $backendMock->expects($this->never())->method('save');
        $frontend = new \Magento\Framework\Cache\Core(array('disable_save' => true));
        $frontend->setBackend($backendMock);
        $result = $frontend->save('data', 'id');
        $this->assertTrue($result);
    }

    public function testSaveNoCaching()
    {
        $backendMock = $this->getMock('Zend_Cache_Backend_BlackHole');
        $backendMock->expects($this->never())->method('save');
        $frontend = new \Magento\Framework\Cache\Core(array('disable_save' => false, 'caching' => false));
        $frontend->setBackend($backendMock);
        $result = $frontend->save('data', 'id');
        $this->assertTrue($result);
    }

    public function testSave()
    {
        $data = 'data';
        $tags = array('abc', '!def', '_ghi');
        $prefix = 'prefix_';
        $prefixedTags = array('prefix_abc', 'prefix__def', 'prefix__ghi');

        $backendMock = $this->getMock('Zend_Cache_Backend_BlackHole');
        $backendMock->expects($this->once())
            ->method('save')
            ->with($data, $this->anything(), $prefixedTags)
            ->will($this->returnValue(true));
        $frontend = new \Magento\Framework\Cache\Core([
            'disable_save'              => false,
            'caching'                   => true,
            'cache_id_prefix'           => $prefix,
            'automatic_cleaning_factor' => 0,
            'write_control'             => false
        ]);
        $frontend->setBackend($backendMock);
        $result = $frontend->save($data, 'id', $tags);
        $this->assertTrue($result);
    }

    public function testClean()
    {
        $mode = 'all';
        $tags = array('abc', '!def', '_ghi');
        $prefix = 'prefix_';
        $prefixedTags = array('prefix_abc', 'prefix__def', 'prefix__ghi');
        $expectedResult = true;

        $backendMock = $this->getMock('Zend_Cache_Backend_BlackHole');
        $backendMock->expects($this->once())
            ->method('clean')
            ->with($mode, $prefixedTags)
            ->will($this->returnValue($expectedResult));
        $frontend = new \Magento\Framework\Cache\Core([
            'caching'         => true,
            'cache_id_prefix' => $prefix
        ]);
        $frontend->setBackend($backendMock);

        $result = $frontend->clean($mode, $tags);
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetIdsMatchingTags()
    {
        $tags = array('abc', '!def', '_ghi');
        $prefix = 'prefix_';
        $prefixedTags = array('prefix_abc', 'prefix__def', 'prefix__ghi');
        $ids = array('id', 'id2', 'id3');

        $backendMock = $this->getMock('Magento\Framework\Cache\CoreMock');
        $backendMock->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with($prefixedTags)
            ->will($this->returnValue($ids));
        $backendMock->expects($this->any())
            ->method('getCapabilities')
            ->will($this->returnValue(['tags' => true]));
        $frontend = new \Magento\Framework\Cache\Core([
            'caching'         => true,
            'cache_id_prefix' => $prefix
        ]);
        $frontend->setBackend($backendMock);

        $result = $frontend->getIdsMatchingTags($tags);
        $this->assertEquals($ids, $result);
    }

    public function testGetIdsNotMatchingTags()
    {
        $tags = array('abc', '!def', '_ghi');
        $prefix = 'prefix_';
        $prefixedTags = array('prefix_abc', 'prefix__def', 'prefix__ghi');
        $ids = array('id', 'id2', 'id3');

        $backendMock = $this->getMock('Magento\Framework\Cache\CoreMock');
        $backendMock->expects($this->once())
            ->method('getIdsNotMatchingTags')
            ->with($prefixedTags)
            ->will($this->returnValue($ids));
        $backendMock->expects($this->any())
            ->method('getCapabilities')
            ->will($this->returnValue(['tags' => true]));
        $frontend = new \Magento\Framework\Cache\Core([
            'caching'         => true,
            'cache_id_prefix' => $prefix
        ]);
        $frontend->setBackend($backendMock);

        $result = $frontend->getIdsNotMatchingTags($tags);
        $this->assertEquals($ids, $result);
    }

}

abstract class CoreMock extends \Zend_Cache_Backend implements \Zend_Cache_Backend_ExtendedInterface
{
}
