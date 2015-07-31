<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\StartUpdater;

class StartUpdaterTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        /** @var $controller StartUpdater */
        $controller = new StartUpdater($filesystem, $updater);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testUpdateActionSuccessUpdate()
    {
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        /** @var $controller StartUpdater */
        $controller = new StartUpdater($filesystem, $updater);
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);
        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"packages":[{"name":"vendor\/package","version":"1.0"}],"type":"cm"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"update"}');
        $controller->setEvent($mvcEvent);
        $controller->dispatch($request, $response);
        $controller->updateAction();
    }

    public function testUpdateActionSuccessUpgrade()
    {
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        /** @var $controller StartUpdater */
        $controller = new StartUpdater($filesystem, $updater);
        $request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);
        $mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $content = '{"packages":[{"name":"vendor\/package","version":"1.0"}],"type":"su"}';
        $request->expects($this->any())->method('getContent')->willReturn($content);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"upgrade"}');
        $controller->setEvent($mvcEvent);
        $controller->dispatch($request, $response);
        $controller->updateAction();
    }
}
