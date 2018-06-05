<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ObjectManager;

class ConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigCache
     */
    protected $_configCache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheFrontendMock;

    protected function setUp()
    {
        $this->_cacheFrontendMock = $this->getMock('\Magento\Framework\Cache\FrontendInterface');
        $this->_configCache = new \Magento\Framework\App\ObjectManager\ConfigCache($this->_cacheFrontendMock);
    }

    protected function tearDown()
    {
        unset($this->_configCache);
    }

    public function testGet()
    {
        $key = 'key';
        $this->_cacheFrontendMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'diConfig' . $key
        )->will(
            $this->returnValue(false)
        );
        $this->assertEquals(false, $this->_configCache->get($key));
    }

    public function testSave()
    {
        $key = 'key';
        $config = ['config'];
        $this->_cacheFrontendMock->expects($this->once())->method('save')->with(serialize($config), 'diConfig' . $key);
        $this->_configCache->save($config, $key);
    }
}
