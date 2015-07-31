<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Model\Navigation;
use Magento\Setup\Controller\StartUpdater;

class StartUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\Updater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $updater;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var Navigation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $navigation;

    /**
     * @var StartUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controller;
    
    public function setUp()
    {
        $this->updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->navigation = $this->getMock('Magento\Setup\Model\Navigation', [], [], '', false);
        $this->controller = new StartUpdater($this->filesystem, $this->navigation, $this->updater);
        $this->navigation->expects($this->any())
            ->method('getMenuItems')
            ->willReturn([['title' => 'A', 'type' => 'cm'], ['title' => 'B', 'type' => 'su']]);
    }
    
    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testUpdateInvalidRequest()
    {
        /** @var \Zend\Http\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);
        /** @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"packages":[{"name":"vendor\/package"}],"type":"cm"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);
        $this->filesystem->expects($this->never())->method('getDirectoryWrite');
        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->controller->updateAction();
    }

    public function testUpdateMissingPackageInfo()
    {
        /** @var \Zend\Http\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);
        /** @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"packages":"test","type":"cm"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);
        $this->filesystem->expects($this->never())->method('getDirectoryWrite');
        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->controller->updateAction();
    }

    public function testUpdateActionSuccessUpdate()
    {
        /** @var \Zend\Http\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);
        /** @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"packages":[{"name":"vendor\/package","version":"1.0"}],"type":"cm"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"update","titles":["A"]}');
        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->controller->updateAction();
    }

    public function testUpdateActionSuccessUpgrade()
    {
        /** @var \Zend\Http\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);
        /** @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"packages":[{"name":"vendor\/package","version":"1.0"}],"type":"su"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"upgrade","titles":["B"]}');
        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->controller->updateAction();
    }
}
