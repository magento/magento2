<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Route\ConfigInterface;

use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Route\ConfigInterface\Proxy;
use Magento\Framework\ObjectManager\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    /**
     * @var Proxy
     */
    protected $_proxy;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $_object;

    protected function setUp(): void
    {
        $this->_object = $this->createPartialMock(
            ConfigInterface::class,
            ['getRouteFrontName', 'getRouteByFrontName', 'getModulesByFrontName']
        );

        $objectManager = $this->createPartialMock(ObjectManager::class, ['get']);
        $objectManager->expects($this->once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->_object);

        $this->_proxy = new Proxy(
            $objectManager,
            ConfigInterface::class
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
