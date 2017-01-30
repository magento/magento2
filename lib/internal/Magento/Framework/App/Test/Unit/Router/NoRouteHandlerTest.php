<?php
/**
 * Tests Magento\Framework\App\Router\NoRouteHandler
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\App\Test\Unit\Router;

class NoRouteHandlerTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Router\NoRouteHandler
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    private $requestMock;

    public function setUp()
    {
        parent::setUp();
        $this->configMock = $this->basicMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->requestMock = $this->basicMock('Magento\Framework\App\Request\Http');
        $this->model = $this->objectManager->getObject('Magento\Framework\App\Router\NoRouteHandler',
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
