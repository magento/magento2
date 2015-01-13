<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Router\Route;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
    }

    /**
     * @test
     */
    public function testCreateRoute()
    {
        $routerClass = 'router';

        $router = $this->getMockBuilder('Zend_Controller_Router_Route_Interface')
            ->setMockClassName($routerClass)
            ->getMock();

        $parameterRoute    = 'route';
        $parameterDefaults = 'defaults';
        $parameterRegs     = 'regs';
        $parameterLocale   = 'locale';

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(
                $this->logicalOr(
                    $routerClass,
                    [
                        'route'    => $parameterRoute,
                        'defaults' => $parameterDefaults,
                        'regs'     => $parameterRegs,
                        'locale'   => $parameterLocale,
                    ]
                )
            )
            ->will($this->returnValue($router));

        $object = new \Magento\Framework\Controller\Router\Route\Factory($this->objectManager);
        $expectedRouter = $object->createRoute(
            $routerClass,
            $parameterRoute,
            $parameterDefaults,
            $parameterRegs,
            $parameterLocale
        );

        $this->assertInstanceOf($routerClass, $expectedRouter);
        $this->assertInstanceOf('Zend_Controller_Router_Route_Interface', $expectedRouter);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function testCreateRouteNegative()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new \StdClass()));

        $object = new \Magento\Framework\Controller\Router\Route\Factory($this->objectManager);
        $object->createRoute(
            'routerClass',
            'router'
        );
    }
}
