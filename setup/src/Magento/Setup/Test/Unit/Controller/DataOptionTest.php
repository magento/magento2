<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Http\PhpEnvironment\Request
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Http\PhpEnvironment\Response
     */
    private $response;

    /**
     * @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mvcEvent;

    /**
     * @var DataOption
     */
    private $controller;

    public function setUp()
    {
        $this->request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $this->response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $this->uninstallCollector = $this->getMock(\Magento\Setup\Model\UninstallCollector::class, [], [], '', false);
        $this->controller = new DataOption($this->uninstallCollector);

        $this->mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
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
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testNoHasUninstallAction()
    {
        $this->request->expects($this->any())->method('getContent')->willReturn('{}');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->uninstallCollector->expects($this->never())->method('collectUninstall')->with(["some_module"]);
        $this->assertFalse($this->controller->hasUninstallAction()->getVariable("hasUninstall"));
    }

    /**
     * @param string $content
     * @param array $expected
     * @param bool $result
     * @dataProvider hasUninstallActionDataProvider
     */
    public function testHasUninstallAction($content, $expected, $result)
    {
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);

        $this->uninstallCollector
            ->expects($this->once())
            ->method('collectUninstall')
            ->with(["some_module"])
            ->willReturn($expected);

        $this->assertSame($result, $this->controller->hasUninstallAction()->getVariable("hasUninstall"));
    }

    /**
     * @return array
     */
    public function hasUninstallActionDataProvider()
    {
        $content = '{"moduleName": "some_module"}';
        return [
            'module has uninstall class' => [$content, ['module'], true],
            'module does not have uninstall class' => [$content, [], false],
        ];
    }
}
