<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\Module\FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleList;

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

    /**
     * @var \Zend\Http\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Zend\Http\PhpEnvironment\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mvcEvent;
    
    public function setUp()
    {
        $this->updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $this->fullModuleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->navigation = $this->getMock('Magento\Setup\Model\Navigation', [], [], '', false);
        $this->controller = new StartUpdater(
            $this->filesystem,
            $this->navigation,
            $this->updater,
            $this->fullModuleList
        );
        $this->navigation->expects($this->any())
            ->method('getMenuItems')
            ->willReturn([
                ['title' => 'A', 'type' => 'update'],
                ['title' => 'B', 'type' => 'upgrade'],
                ['title' => 'C', 'type' => 'enable'],
                ['title' => 'D', 'type' => 'disable'],
            ]);
        $this->request = $this->getMock('\Zend\Http\PhpEnvironment\Request', [], [], '', false);
        $this->response = $this->getMock('\Zend\Http\PhpEnvironment\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);
        $this->mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $this->mvcEvent->expects($this->any())
            ->method('setRequest')
            ->with($this->request)
            ->willReturn($this->mvcEvent);
        $this->mvcEvent->expects($this->any())
            ->method('setResponse')
            ->with($this->response)
            ->willReturn($this->mvcEvent);
        $this->mvcEvent->expects($this->any())
            ->method('setTarget')
            ->with($this->controller)
            ->willReturn($this->mvcEvent);
        $this->mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
    }
    
    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testUpdateInvalidRequestNoParam()
    {
        $content = '{}';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->filesystem->expects($this->never())->method('getDirectoryWrite');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateInvalidRequestNotArray()
    {
        $content = '{"packages":"test","type":"update"}';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->filesystem->expects($this->never())->method('getDirectoryWrite');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateInvalidRequestMissingVersion()
    {
        $content = '{"packages":[{"name":"vendor\/package"}],"type":"update"}';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->filesystem->expects($this->never())->method('getDirectoryWrite');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateInvalidRequestMissingDataOption()
    {
        $content = '{"packages":[{"name":"vendor\/package", "version": "1.0.0"}],"type":"uninstall"}';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->filesystem->expects($this->never())->method('getDirectoryWrite');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateMissingPackageInfo()
    {
        $content = '{"packages":"test","type":"update"}';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->filesystem->expects($this->never())->method('getDirectoryWrite');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateActionSuccessUpdate()
    {
        $content = '{"packages":[{"name":"vendor\/package","version":"1.0"}],"type":"update",'
            . '"headerTitle": "Update package 1" }';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"update","headerTitle":"Update package 1","titles":["A"]}');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateActionSuccessUpgrade()
    {
        $content = '{"packages":[{"name":"vendor\/package","version":"1.0"}],"type":"upgrade",'
            . '"headerTitle": "System Upgrade" }';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"upgrade","headerTitle":"System Upgrade","titles":["B"]}');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateActionSuccessEnable()
    {
        $content = '{"packages":[{"name":"vendor\/package"}],"type":"enable",'
            . '"headerTitle": "Enable Package 1" }';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->fullModuleList->expects($this->once())->method('has')->willReturn(true);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"enable","headerTitle":"Enable Package 1","titles":["C"]}');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    public function testUpdateActionSuccessDisable()
    {
        $content = '{"packages":[{"name":"vendor\/package"}],"type":"disable",'
            . '"headerTitle": "Disable Package 1" }';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->fullModuleList->expects($this->once())->method('has')->willReturn(true);
        $write = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface', [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($write);
        $write->expects($this->once())
            ->method('writeFile')
            ->with('.type.json', '{"type":"disable","headerTitle":"Disable Package 1","titles":["D"]}');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }
}
