<?php
/**
 * Tests Magento\Framework\App\Router\Base
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Router;

class BaseTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Router\Base
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Route\ConfigInterface
     */
    private $routeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State
     */
    private $appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Router\ActionList
     */
    private $actionListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ActionFactory
     */
    private $actionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Code\NameBuilder
     */
    private $nameBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DefaultPathInterface
     */
    private $defaultPathMock;

    protected function setUp()
    {
        parent::setUp();
        // Create mocks
        $this->requestMock = $this->basicMock(\Magento\Framework\App\Request\Http::class);
        $this->routeConfigMock = $this->basicMock(\Magento\Framework\App\Route\ConfigInterface::class);
        $this->appStateMock = $this->basicMock(\Magento\Framework\App\State::class);
        $this->actionListMock = $this->basicMock(\Magento\Framework\App\Router\ActionList::class);
        $this->actionFactoryMock = $this->basicMock(\Magento\Framework\App\ActionFactory::class);
        $this->nameBuilderMock = $this->basicMock(\Magento\Framework\Code\NameBuilder::class);
        $this->defaultPathMock = $this->basicMock(\Magento\Framework\App\DefaultPathInterface::class);

        // Prepare SUT
        $mocks = [
            'actionList' => $this->actionListMock,
            'actionFactory' => $this->actionFactoryMock,
            'routeConfig' => $this->routeConfigMock,
            'appState' => $this->appStateMock,
            'nameBuilder' => $this->nameBuilderMock,
            'defaultPath' => $this->defaultPathMock,
        ];
        $this->model = $this->objectManager->getObject(\Magento\Framework\App\Router\Base::class, $mocks);
    }

    public function testMatch()
    {
        // Test Data
        $actionInstance = 'action instance';
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $actionClassName = \Magento\Framework\App\Action\Action::class;
        $moduleName = 'module name';
        $moduleList = [$moduleName];

        // Stubs
        $this->requestMock->expects($this->any())->method('getModuleName')->willReturn($moduleFrontName);
        $this->requestMock->expects($this->any())->method('getControllerName')->willReturn($actionPath);
        $this->requestMock->expects($this->any())->method('getActionName')->willReturn($actionName);
        $this->routeConfigMock->expects($this->any())->method('getModulesByFrontName')->willReturn($moduleList);
        $this->appStateMock->expects($this->any())->method('isInstalled')->willReturn(true);
        $this->actionListMock->expects($this->any())->method('get')->willReturn($actionClassName);
        $this->actionFactoryMock->expects($this->any())->method('create')->willReturn($actionInstance);

        // Expectations and Test
        $this->requestExpects('setModuleName', $moduleFrontName)
            ->requestExpects('setControllerName', $actionPath)
            ->requestExpects('setActionName', $actionName)
            ->requestExpects('setControllerModule', $moduleName);

        $this->assertSame($actionInstance, $this->model->match($this->requestMock));
    }

    public function testMatchUseParams()
    {
        // Test Data
        $actionInstance = 'action instance';
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $actionClassName = \Magento\Framework\App\Action\Action::class;
        $moduleName = 'module name';
        $moduleList = [$moduleName];
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        // Stubs
        $this->requestMock->expects($this->any())->method('getPathInfo')->willReturn($paramList);
        $this->routeConfigMock->expects($this->any())->method('getModulesByFrontName')->willReturn($moduleList);
        $this->appStateMock->expects($this->any())->method('isInstalled')->willReturn(false);
        $this->actionListMock->expects($this->any())->method('get')->willReturn($actionClassName);
        $this->actionFactoryMock->expects($this->any())->method('create')->willReturn($actionInstance);

        // Expectations and Test
        $this->requestExpects('setModuleName', $moduleFrontName)
            ->requestExpects('setControllerName', $actionPath)
            ->requestExpects('setActionName', $actionName)
            ->requestExpects('setControllerModule', $moduleName);

        $this->assertSame($actionInstance, $this->model->match($this->requestMock));
    }

    public function testMatchUseDefaultPath()
    {
        // Test Data
        $actionInstance = 'action instance';
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $actionClassName = \Magento\Framework\App\Action\Action::class;
        $moduleName = 'module name';
        $moduleList = [$moduleName];

        // Stubs
        $defaultReturnMap = [
            ['module', $moduleFrontName],
            ['controller', $actionPath],
            ['action', $actionName],
        ];
        $this->defaultPathMock->expects($this->any())->method('getPart')->willReturnMap($defaultReturnMap);
        $this->routeConfigMock->expects($this->any())->method('getModulesByFrontName')->willReturn($moduleList);
        $this->appStateMock->expects($this->any())->method('isInstalled')->willReturn(false);
        $this->actionListMock->expects($this->any())->method('get')->willReturn($actionClassName);
        $this->actionFactoryMock->expects($this->any())->method('create')->willReturn($actionInstance);

        // Expectations and Test
        $this->requestExpects('setModuleName', $moduleFrontName)
            ->requestExpects('setControllerName', $actionPath)
            ->requestExpects('setActionName', $actionName)
            ->requestExpects('setControllerModule', $moduleName);

        $this->assertSame($actionInstance, $this->model->match($this->requestMock));
    }

    public function testMatchEmptyModuleList()
    {
        // Test Data
        $actionInstance = 'action instance';
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $actionClassName = \Magento\Framework\App\Action\Action::class;
        $emptyModuleList = [];

        // Stubs
        $this->requestMock->expects($this->any())->method('getModuleName')->willReturn($moduleFrontName);
        $this->routeConfigMock->expects($this->any())->method('getModulesByFrontName')->willReturn($emptyModuleList);
        $this->requestMock->expects($this->any())->method('getControllerName')->willReturn($actionPath);
        $this->requestMock->expects($this->any())->method('getActionName')->willReturn($actionName);
        $this->appStateMock->expects($this->any())->method('isInstalled')->willReturn(false);
        $this->actionListMock->expects($this->any())->method('get')->willReturn($actionClassName);
        $this->actionFactoryMock->expects($this->any())->method('create')->willReturn($actionInstance);

        // Test
        $this->assertNull($this->model->match($this->requestMock));
    }

    public function testMatchEmptyActionInstance()
    {
        // Test Data
        $nullActionInstance = null;
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $actionClassName = \Magento\Framework\App\Action\Action::class;
        $moduleName = 'module name';
        $moduleList = [$moduleName];

        // Stubs
        $this->requestMock->expects($this->any())->method('getModuleName')->willReturn($moduleFrontName);
        $this->routeConfigMock->expects($this->any())->method('getModulesByFrontName')->willReturn($moduleList);
        $this->requestMock->expects($this->any())->method('getControllerName')->willReturn($actionPath);
        $this->requestMock->expects($this->any())->method('getActionName')->willReturn($actionName);
        $this->appStateMock->expects($this->any())->method('isInstalled')->willReturn(false);
        $this->actionListMock->expects($this->any())->method('get')->willReturn($actionClassName);
        $this->actionFactoryMock->expects($this->any())->method('create')->willReturn($nullActionInstance);

        // Expectations and Test
        $this->assertNull($this->model->match($this->requestMock));
    }

    public function testGetActionClassName()
    {
        $className = 'name of class';
        $module = 'module';
        $prefix = 'Controller';
        $actionPath = 'action path';
        $this->nameBuilderMock->expects($this->once())
            ->method('buildClassName')
            ->with([$module, $prefix, $actionPath])
            ->willReturn($className);
        $this->assertEquals($className, $this->model->getActionClassName($module, $actionPath));
    }

    /**
     * Generate a stub with an expected usage for the request mock object
     *
     * @param string $method
     * @param string $with
     * @return $this
     */
    private function requestExpects($method, $with)
    {
        $this->requestMock->expects($this->once())
            ->method($method)
            ->with($with);
        return $this;
    }
}
