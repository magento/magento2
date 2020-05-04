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
use Magento\Setup\Controller\StartUpdater;
use Magento\Setup\Model\PayloadValidator;
use Magento\Setup\Model\UpdaterTaskCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Setup\Controller\StartUpdater
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StartUpdaterTest extends TestCase
{
    /**
     * @var StartUpdater|MockObject
     */
    private $controller;

    /**
     * @var Request|MockObject
     */
    private $request;

    /**
     * @var Response|MockObject
     */
    private $response;

    /**
     * @var MvcEvent|MockObject
     */
    private $mvcEvent;

    /**
     * @var PayloadValidator|MockObject
     */
    private $payloadValidator;

    /**
     * @var UpdaterTaskCreator|MockObject
     */
    private $updaterTaskCreator;

    protected function setUp(): void
    {
        $this->payloadValidator = $this->createMock(PayloadValidator::class);
        $this->updaterTaskCreator = $this->createMock(UpdaterTaskCreator::class);

        $this->controller = new StartUpdater(
            $this->updaterTaskCreator,
            $this->payloadValidator
        );
        $this->request = $this->createMock(Request::class);
        $this->response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);
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

    /**
     * @param string $content
     * @param int $payload
     * @dataProvider updateInvalidRequestDataProvider
     */
    public function testUpdateInvalidRequest($content, $payload)
    {
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->payloadValidator->expects($this->exactly($payload))->method('validatePayload');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }

    /**
     * @return array
     */
    public function updateInvalidRequestDataProvider()
    {
        return [
            'NoParmas' => ['{}', 0],
            'NoArray' => ['{"packages":"test","type":"update"}', 0],
            'NoVersion' => ['{"packages":[{"name":"vendor\/package"}],"type":"update"}', 1],
            'NoDataOption' => ['{"packages":[{"name":"vendor\/package", "version": "1.0.0"}],"type":"uninstall"}', 1],
            'NoPackageInfo' => ['{"packages":"test","type":"update"}', 0]
        ];
    }

    public function testUpdateActionSuccess()
    {
        $content = '{"packages":[{"name":"vendor\/package","version":"1.0"}],"type":"update",'
            . '"headerTitle": "Update package 1" }';
        $this->request->expects($this->any())->method('getContent')->willReturn($content);
        $this->payloadValidator->expects($this->once())->method('validatePayload')->willReturn('');
        $this->updaterTaskCreator->expects($this->once())->method('createUpdaterTasks')->willReturn('');
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $this->response);
        $this->controller->updateAction();
    }
}
