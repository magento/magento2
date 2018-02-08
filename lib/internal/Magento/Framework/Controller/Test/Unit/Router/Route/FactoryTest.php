<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Router\Route;

use \Magento\Framework\Controller\Router\Route\Factory;

use Magento\Framework\Controller\Router\Route\Factory as RouteFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManager;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var RouteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $objectManager = new ObjectManager($this);
        $this->factory = $objectManager->getObject(
            'Magento\Framework\Controller\Router\Route\Factory',
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

        $router = $this->getMockBuilder('Magento\Framework\App\RouterInterface')
            ->setMockClassName($routeClass)
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($routeClass, ['route' => $paramRoute])
            ->will($this->returnValue($router));

        $result = $this->factory->createRoute($routeClass, $paramRoute);

        $this->assertInstanceOf($routeClass, $result);
        $this->assertInstanceOf('Magento\Framework\App\RouterInterface', $result);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @return void
     */
    public function testCreateRouteNegative()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new \StdClass()));

        $object = new Factory($this->objectManager);
        $object->createRoute(
            'routerClass',
            'router'
        );
    }
}
