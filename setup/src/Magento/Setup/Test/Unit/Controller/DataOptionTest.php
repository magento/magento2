<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\DataOption;
use Magento\Setup\Model\UninstallCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataOptionTest extends TestCase
{
    /**
     * @var MockObject|UninstallCollector
     */
    private $uninstallCollector;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject|Response
     */
    private $response;

    /**
     * @var MvcEvent|MockObject
     */
    private $mvcEvent;

    /**
     * @var DataOption
     */
    private $controller;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $this->uninstallCollector = $this->createMock(UninstallCollector::class);
        $this->controller = new DataOption($this->uninstallCollector);

        $this->mvcEvent = $this->createMock(MvcEvent::class);
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
        $this->mvcEvent->expects($this->any())->method('getName')->willReturn('dispatch');
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
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
