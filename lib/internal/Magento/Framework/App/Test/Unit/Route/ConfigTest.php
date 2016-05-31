<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Route;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Route\Config
     */
    protected $_config;

    /**
     * @var Cache_Mock_Wrapper
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_areaList;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock('Magento\Framework\App\Route\Config\Reader', [], [], '', false);
        $this->_cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');
        $this->_configScopeMock = $this->getMock('\Magento\Framework\Config\ScopeInterface');
        $this->_areaList = $this->getMock('\Magento\Framework\App\AreaList', [], [], '', false);
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'getCurrentScope'
        )->will(
            $this->returnValue('areaCode')
        );
        $this->_config = new \Magento\Framework\App\Route\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_configScopeMock,
            $this->_areaList
        );
    }

    public function testGetRouteFrontNameIfCacheIfRouterIdNotExist()
    {
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'areaCode::RoutesConfig'
        )->will(
            $this->returnValue(serialize(['expected']))
        );
        $this->assertEquals('routerCode', $this->_config->getRouteFrontName('routerCode'));
    }

    public function testGetRouteByFrontName()
    {
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'areaCode::RoutesConfig'
        )->will(
            $this->returnValue(serialize(['routerCode' => ['frontName' => 'routerName']]))
        );

        $this->assertEquals('routerCode', $this->_config->getRouteByFrontName('routerName'));

        // check internal caching in $this->_routes array
        $this->assertEquals('routerCode', $this->_config->getRouteByFrontName('routerName'));
    }

    public function testGetRouteByFrontNameNoRoutes()
    {
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'areaCode::RoutesConfig'
        )->will(
            $this->returnValue(serialize([]))
        );

        $this->assertFalse($this->_config->getRouteByFrontName('routerName'));

        // check caching in $this->_routes array
        $this->assertFalse($this->_config->getRouteByFrontName('routerName'));
    }

    public function testGetRouteByFrontNameNoCache()
    {
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'scope::RoutesConfig'
        )->will(
            $this->returnValue(serialize(false))
        );

        $routes = [
            'routerCode' => [
                'frontName' => 'routerName',
            ],
        ];

        $routers = [
            'default_router' => [
                'routes' => $routes,
            ],
        ];

        $this->_readerMock->expects(
            $this->once()
        )->method(
            'read'
        )->with(
            'scope'
        )->will(
            $this->returnValue($routers)
        );

        $this->_areaList->expects(
            $this->once()
        )->method(
            'getDefaultRouter'
        )->with(
            'scope'
        )->will(
            $this->returnValue('default_router')
        );

        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            serialize($routes),
            'scope::RoutesConfig'
        );

        $this->assertEquals('routerCode', $this->_config->getRouteByFrontName('routerName', 'scope'));

        // check caching in $this->_routes array
        $this->assertEquals('routerCode', $this->_config->getRouteByFrontName('routerName', 'scope'));
    }

    public function testGetModulesByFrontName()
    {
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'areaCode::RoutesConfig'
        )->will(
            $this->returnValue(
                serialize(['routerCode' => ['frontName' => 'routerName', 'modules' => ['Module1']]])
            )
        );
        $this->assertEquals(['Module1'], $this->_config->getModulesByFrontName('routerName'));
    }
}
