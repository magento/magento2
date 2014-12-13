<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\Config;

class ProxyConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\Config\ProxyConfig
     */
    protected $_proxyConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    protected function setUp()
    {
        $this->_config = $this->getMock('Magento\Framework\ObjectManager\ConfigInterface', [], [], '', false);
        $this->_proxyConfig = new \Magento\Framework\ObjectManager\Config\ProxyConfig($this->_config);
    }

    public function testSetRelations()
    {
        $relation = $this->getMock('Magento\Framework\ObjectManager\RelationsInterface', [], [], '', false);
        $this->_config->expects($this->once())
            ->method('setRelations')
            ->with($relation);
        $this->_proxyConfig->setRelations($relation);
    }

    public function testSetCache()
    {
        $configCache = $this->getMock('Magento\Framework\ObjectManager\ConfigCacheInterface', [], [], '', false);
        $this->_config->expects($this->once())
            ->method('setCache')
            ->with($configCache);
        $this->_proxyConfig->setCache($configCache);
    }

    public function testGetArguments()
    {
        $stringArgs = 'string';
        $this->_config->expects($this->once())
            ->method('getArguments')
            ->with($stringArgs)
            ->willReturn([]);
        $this->assertEquals([], $this->_proxyConfig->getArguments($stringArgs));
    }

    /**
     * @dataProvider isSharedDataProvider
     */
    public function testIsShared($returnValue)
    {
        $type = 'string';
        $this->_config->expects($this->once())
            ->method('isShared')
            ->with($type)
            ->willReturn($returnValue);
        $this->assertEquals($returnValue, $this->_proxyConfig->isShared($type));
    }

    /**
     * @return array
     */
    public function isSharedDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testGetInstanceType()
    {
        $instanceName = 'string';
        $this->_config->expects($this->once())
            ->method('getInstanceType')
            ->with($instanceName)
            ->willReturn('instanceType');
        $this->assertEquals('instanceType', $this->_proxyConfig->getInstanceType($instanceName));
    }

    public function testGetPreference()
    {
        $type = 'string';
        $this->_config->expects($this->once())
            ->method('getPreference')
            ->with($type)
            ->willReturn('someString');
        $this->assertEquals('someString', $this->_proxyConfig->getPreference($type));
    }

    public function testExtend()
    {
        $this->_config->expects($this->once())
            ->method('extend')
            ->with([]);
        $this->_proxyConfig->extend([]);
    }

    public function testGetVirtualTypes()
    {
        $this->_config->expects($this->once())
            ->method('getVirtualTypes')
            ->willReturn([]);
        $this->assertEquals([], $this->_proxyConfig->getVirtualTypes());
    }
}
