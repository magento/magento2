<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Route\ConfigInterface;

class ProxyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Route\ConfigInterface\Proxy
     */
    protected $_proxy;

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->createPartialMock(
            \Magento\Framework\App\Route\ConfigInterface::class,
            ['getRouteFrontName', 'getRouteByFrontName', 'getModulesByFrontName']
        );

        $objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['get']);
        $objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\App\Route\ConfigInterface::class)
            ->will($this->returnValue($this->_object));

        $this->_proxy = new \Magento\Framework\App\Route\ConfigInterface\Proxy(
            $objectManager,
            \Magento\Framework\App\Route\ConfigInterface::class
        );
    }

    public function testGetRouteFrontName()
    {
        $routeId = 1;
        $scope = null;
        $this->_object->expects($this->once())->method('getRouteFrontName')->with($routeId, $scope);
        $this->_proxy->getRouteFrontName($routeId, $scope);
    }

    public function testGetRouteByFrontName()
    {
        $frontName = 'route';
        $scope = null;
        $this->_object->expects($this->once())->method('getRouteByFrontName')->with($frontName, $scope);
        $this->_proxy->getRouteByFrontName($frontName, $scope);
    }

    public function testGetModulesByFrontName()
    {
        $frontName = 'route';
        $scope = null;
        $this->_object->expects($this->once())->method('getModulesByFrontName')->with($frontName, $scope);
        $this->_proxy->getModulesByFrontName($frontName, $scope);
    }
}
