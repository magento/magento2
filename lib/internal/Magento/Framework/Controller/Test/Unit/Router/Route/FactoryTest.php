<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Controller\Test\Unit\Router\Route;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\Controller\Router\Route\Factory;
use Magento\Framework\Controller\Router\Route\Factory as RouteFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManager;

    /**
     * @var RouteFactory|MockObject
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

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

        $router = $this->getMockBuilder(RouterInterface::class)
            ->setMockClassName($routeClass)
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($routeClass, ['route' => $paramRoute])
            ->willReturn($router);

        $result = $this->factory->createRoute($routeClass, $paramRoute);

        $this->assertInstanceOf($routeClass, $result);
        $this->assertInstanceOf(RouterInterface::class, $result);
    }

    /**
     * @test
     * @return void
     */
    public function testCreateRouteNegative()
    {
        $this->expectException('LogicException');
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
