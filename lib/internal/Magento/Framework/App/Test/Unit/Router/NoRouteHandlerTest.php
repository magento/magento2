<?php declare(strict_types=1);
/**
 * Tests Magento\Framework\App\Router\NoRouteHandler
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Router;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Router\NoRouteHandler;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class NoRouteHandlerTest extends BaseTestCase
{
    /**
     * @var NoRouteHandler
     */
    private $model;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $configMock;

    /**
     * @var MockObject|Http
     */
    private $requestMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configMock = $this->basicMock(ScopeConfigInterface::class);
        $this->requestMock = $this->basicMock(Http::class);
        $this->model = $this->objectManager->getObject(
            NoRouteHandler::class,
            [
                'config' => $this->configMock,
            ]
        );
    }

    public function testProcessDefault()
    {
        // Default path from config
        $default = 'moduleName/actionPath/actionName';
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('web/default/no_route', 'default')
            ->willReturn($default);

        // Set expectations
        $this->requestMock->expects($this->once())
            ->method('setModuleName')
            ->with('moduleName')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setControllerName')
            ->with('actionPath')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setActionName')
            ->with('actionName')
            ->willReturnSelf();

        // Test
        $this->assertTrue($this->model->process($this->requestMock));
    }

    public function testProcessNoDefault()
    {
        // Default path from config
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('web/default/no_route', 'default')
            ->willReturn(null);

        // Set expectations
        $this->requestMock->expects($this->once())
            ->method('setModuleName')
            ->with('core')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setControllerName')
            ->with('index')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setActionName')
            ->with('index')
            ->willReturnSelf();

        // Test
        $this->assertTrue($this->model->process($this->requestMock));
    }
}
