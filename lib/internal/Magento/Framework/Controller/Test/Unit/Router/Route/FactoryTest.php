<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Router\Route;

use \Magento\Framework\Controller\Router\Route\Factory;

use Magento\Framework\Controller\Router\Route\Factory as RouteFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var RouteFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->factory = $objectManager->getObject(
            \Magento\Framework\Controller\Router\Route\Factory::class,
            [
                'objectManager' => $this->objectManager,
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function testCreateRoute()
    {
        $routeClass = 'router';
        $paramRoute = 'route';

        $router = $this->getMockBuilder(\Magento\Framework\App\RouterInterface::class)
            ->setMockClassName($routeClass)
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($routeClass, ['route' => $paramRoute])
            ->willReturn($router);

        $result = $this->factory->createRoute($routeClass, $paramRoute);

        $this->assertInstanceOf($routeClass, $result);
        $this->assertInstanceOf(\Magento\Framework\App\RouterInterface::class, $result);
    }

    /**
     * @test
     * @return void
     */
    public function testCreateRouteNegative()
    {
        $this->expectException(\LogicException::class);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->willReturn(new \StdClass());

        $object = new Factory($this->objectManager);
        $object->createRoute(
            'routerClass',
            'router'
        );
    }
}
