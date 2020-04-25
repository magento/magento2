<?php declare(strict_types=1);
/**
 * Tests Magento\Framework\App\Router\Base
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Router;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\App\Router\Base;
use Magento\Framework\App\State;
use Magento\Framework\Code\NameBuilder;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base router unit test.
 */
class BaseTest extends BaseTestCase
{
    /**
     * @var Base
     */
    private $model;

    /**
     * @var MockObject|Http
     */
    private $requestMock;

    /**
     * @var MockObject|ConfigInterface
     */
    private $routeConfigMock;

    /**
     * @var MockObject|State
     */
    private $appStateMock;

    /**
     * @var MockObject|ActionList
     */
    private $actionListMock;

    /**
     * @var MockObject|ActionFactory
     */
    private $actionFactoryMock;

    /**
     * @var MockObject|NameBuilder
     */
    private $nameBuilderMock;

    /**
     * @var MockObject|DefaultPathInterface
     */
    private $defaultPathMock;

    protected function setUp(): void
    {
        parent::setUp();
        // Create mocks
        $this->requestMock = $this->basicMock(Http::class);
        $this->routeConfigMock = $this->basicMock(ConfigInterface::class);
        $this->appStateMock = $this->getMockBuilder(State::class)
            ->addMethods(['isInstalled'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionListMock = $this->basicMock(ActionList::class);
        $this->actionFactoryMock = $this->basicMock(ActionFactory::class);
        $this->nameBuilderMock = $this->basicMock(NameBuilder::class);
        $this->defaultPathMock = $this->basicMock(DefaultPathInterface::class);

        // Prepare SUT
        $mocks = [
            'actionList' => $this->actionListMock,
            'actionFactory' => $this->actionFactoryMock,
            'routeConfig' => $this->routeConfigMock,
            'appState' => $this->appStateMock,
            'nameBuilder' => $this->nameBuilderMock,
            'defaultPath' => $this->defaultPathMock,
        ];
        $this->model = $this->objectManager->getObject(Base::class, $mocks);
    }

    public function testMatch()
    {
        // Test Data
        $actionInstance = 'action instance';
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $actionClassName = Action::class;
        $moduleName = 'module name';
        $moduleList = [$moduleName];
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        // Stubs
        $this->requestMock->expects($this->any())->method('getModuleName')->willReturn($moduleFrontName);
        $this->requestMock->expects($this->any())->method('getControllerName')->willReturn($actionPath);
        $this->requestMock->expects($this->any())->method('getActionName')->willReturn($actionName);
        $this->requestMock->expects($this->any())->method('getPathInfo')->willReturn($paramList);
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
        $actionClassName = Action::class;
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
        $actionClassName = Action::class;
        $moduleName = 'module name';
        $moduleList = [$moduleName];
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        // Stubs
        $defaultReturnMap = [
            ['module', $moduleFrontName],
            ['controller', $actionPath],
            ['action', $actionName],
        ];
        $this->requestMock->expects($this->any())->method('getPathInfo')->willReturn($paramList);
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
        $actionClassName = Action::class;
        $emptyModuleList = [];
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        // Stubs
        $this->requestMock->expects($this->any())->method('getModuleName')->willReturn($moduleFrontName);
        $this->requestMock->expects($this->any())->method('getPathInfo')->willReturn($paramList);
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
        $actionClassName = Action::class;
        $moduleName = 'module name';
        $moduleList = [$moduleName];
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        // Stubs
        $this->requestMock->expects($this->any())->method('getModuleName')->willReturn($moduleFrontName);
        $this->requestMock->expects($this->any())->method('getPathInfo')->willReturn($paramList);
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
