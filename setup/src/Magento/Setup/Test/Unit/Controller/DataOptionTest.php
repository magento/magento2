<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\DataOption;

class DataOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Magento\Setup\Model\UninstallCollector
     */
    private $uninstallCollector;

    /**
     * @var DataOption
     */
    private $controller;

    public function setUp()
    {
        $this->uninstallCollector = $this->getMock('Magento\Setup\Model\UninstallCollector', [], [], '', false);
        $this->controller = new DataOption($this->uninstallCollector);
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testHasUninstallAction()
    {
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);

        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"moduleName": "some_module"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);

        $this->uninstallCollector
            ->expects($this->once())
            ->method('collectUninstall')
            ->with(["some_module"])
            ->willReturn(['module']);

        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->assertTrue($this->controller->hasUninstallAction()->getVariable("hasUninstall"));
    }

    public function testNoUninstallAction()
    {
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);

        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $request->expects($this->any())->method('getContent')->willReturn('');

        $this->uninstallCollector->expects($this->never())->method('collectUninstall');
        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->assertFalse($this->controller->hasUninstallAction()->getVariable("hasUninstall"));
    }

    public function testEmptyUninstallAction()
    {
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);

        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"moduleName": "some_module"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);

        $this->uninstallCollector
            ->expects($this->once())
            ->method('collectUninstall')
            ->with(["some_module"])
            ->willReturn([]);

        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->assertFalse($this->controller->hasUninstallAction()->getVariable("hasUninstall"));
    }
}
