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
use Laminas\View\Model\JsonModel;
use Magento\Framework\App\MaintenanceMode;
use Magento\Setup\Controller\Maintenance;
use Magento\Setup\Controller\ResponseTypeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MaintenanceTest extends TestCase
{

    /**
     * @var MaintenanceMode|MockObject
     */
    private $maintenanceMode;

    /**
     * Controller
     *
     * @var Maintenance
     */
    private $controller;

    protected function setUp(): void
    {
        $this->maintenanceMode = $this->createMock(MaintenanceMode::class);
        $this->controller = new Maintenance($this->maintenanceMode);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->createMock(MvcEvent::class);
        $mvcEvent->expects($this->any())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $mvcEvent->expects($this->any())->method('getName')->willReturn('dispatch');

        $contentArray = '{"disable":false}';
        $request->expects($this->any())->method('getContent')->willReturn($contentArray);

        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
    }

    public function testIndexAction()
    {
        $this->maintenanceMode->expects($this->once())->method('set');
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testIndexActionWithExceptions()
    {
        $this->maintenanceMode->expects($this->once())->method('set')->willThrowException(
            new \Exception("Test error message")
        );
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
        $this->assertArrayHasKey('error', $variables);
        $this->assertEquals("Test error message", $variables['error']);
    }
}
