<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Router;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\App\Router\Base;
use Magento\Framework\App\Router\PathConfigInterface;
use Magento\Framework\Code\NameBuilder;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base router unit test.
 */
class BaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Base
     */
    private $model;

    /**
     * @var MockObject|ConfigInterface
     */
    private $routeConfigMock;

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

    /**
     * @var MockObject|ResponseFactory
     */
    private $responseFactoryMock;

    /**
     * @var MockObject|UrlInterface
     */
    private $urlMock;

    /**
     * @var MockObject|PathConfigInterface
     */
    private $pathConfigMock;

    protected function setUp(): void
    {
        $this->routeConfigMock = $this->createMock(ConfigInterface::class);
        $this->actionListMock = $this->createMock(ActionList::class);
        $this->actionFactoryMock = $this->createMock(ActionFactory::class);
        $this->nameBuilderMock = $this->createMock(NameBuilder::class);
        $this->defaultPathMock = $this->createMock(DefaultPathInterface::class);
        $this->responseFactoryMock = $this->createMock(ResponseFactory::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->pathConfigMock = $this->createMock(PathConfigInterface::class);

        $this->model = new Base(
            $this->actionListMock,
            $this->actionFactoryMock,
            $this->defaultPathMock,
            $this->responseFactoryMock,
            $this->routeConfigMock,
            $this->urlMock,
            $this->nameBuilderMock,
            $this->pathConfigMock
        );
    }

    /**
     * @dataProvider matchDataProvider
     * @param MockObject|Http $requestMock
     * @param string $defaultPath
     * @param string $moduleFrontName
     * @param string $actionPath
     * @param string $actionName
     * @param string $moduleName
     */
    public function testMatch(
        MockObject $requestMock,
        string $defaultPath,
        string $moduleFrontName,
        string $actionPath,
        string $actionName,
        string $moduleName
    ) {
        $actionInstance = 'Magento_TestFramework_ActionInstance';

        $defaultReturnMap = [
            ['module', $moduleFrontName],
            ['controller', $actionPath],
            ['action', $actionName],
        ];
        $this->defaultPathMock->method('getPart')
            ->willReturnMap($defaultReturnMap);
        $this->pathConfigMock->method('getDefaultPath')
            ->willReturn($defaultPath);
        $this->routeConfigMock->expects($this->once())
            ->method('getModulesByFrontName')
            ->with($moduleFrontName)
            ->willReturn([$moduleName]);

        $actionMock = $this->getMockBuilder(ActionInterface::class)
            ->setMockClassName($actionInstance)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->actionListMock->expects($this->once())
            ->method('get')
            ->with($moduleName)
            ->willReturn($actionInstance);
        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->with($actionInstance)
            ->willReturn($actionMock);

        $requestMock->expects($this->once())->method('setModuleName')->with($moduleFrontName);
        $requestMock->expects($this->once())->method('setControllerName')->with($actionPath);
        $requestMock->expects($this->once())->method('setActionName')->with($actionName);
        $requestMock->expects($this->once())->method('setControllerModule')->with($moduleName);

        $this->assertEquals($actionMock, $this->model->match($requestMock));
    }

    public function matchDataProvider(): array
    {
        $moduleFrontName = 'module_front_name';
        $actionPath = 'action_path';
        $actionName = 'action_name';
        $moduleName = 'module_name';
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->atLeastOnce())->method('getModuleName')->willReturn($moduleFrontName);
        $requestMock->expects($this->atLeastOnce())->method('getControllerName')->willReturn($actionPath);
        $requestMock->expects($this->atLeastOnce())->method('getActionName')->willReturn($actionName);
        $requestMock->expects($this->atLeastOnce())->method('getPathInfo')->willReturn($paramList);

        $emptyRequestMock = $this->createMock(Http::class);
        $emptyRequestMock->expects($this->atLeastOnce())->method('getModuleName')->willReturn('');
        $emptyRequestMock->expects($this->atLeastOnce())->method('getControllerName')->willReturn('');
        $emptyRequestMock->expects($this->atLeastOnce())->method('getActionName')->willReturn('');
        $emptyRequestMock->expects($this->atLeastOnce())->method('getPathInfo')->willReturn('');

        $emptyRequestMock2 = clone $emptyRequestMock;
        $emptyRequestMock2->expects($this->once())->method('getOriginalPathInfo')->willReturn('');

        return [
            [$requestMock, '', $moduleFrontName, $actionPath, $actionName, $moduleName],
            [$emptyRequestMock, $paramList, $moduleFrontName, $actionPath, $actionName, $moduleName],
            [$emptyRequestMock2, '', $moduleFrontName, $actionPath, $actionName, $moduleName],
        ];
    }

    public function testMatchEmptyModuleList()
    {
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->atLeastOnce())
            ->method('getModuleName')
            ->willReturn($moduleFrontName);
        $requestMock->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturn($paramList);
        $this->routeConfigMock->expects($this->once())
            ->method('getModulesByFrontName')
            ->with($moduleFrontName)
            ->willReturn([]);
        $this->actionListMock->expects($this->never())->method('get');
        $this->actionFactoryMock->expects($this->never())->method('create');

        $this->assertNull($this->model->match($requestMock));
    }

    public function testMatchEmptyActionInstance()
    {
        $moduleFrontName = 'module front name';
        $actionPath = 'action path';
        $actionName = 'action name';
        $moduleName = 'module name';
        $paramList = $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/';

        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->atLeastOnce())
            ->method('getModuleName')
            ->willReturn($moduleFrontName);
        $requestMock->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturn($paramList);
        $requestMock->expects($this->atLeastOnce())
            ->method('getControllerName')
            ->willReturn($actionPath);
        $requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($actionName);
        $this->routeConfigMock->expects($this->once())
            ->method('getModulesByFrontName')
            ->with($moduleFrontName)
            ->willReturn([$moduleName]);
        $this->actionListMock->expects($this->once())
            ->method('get')
            ->with($moduleName)
            ->willReturn(null);
        $this->actionFactoryMock->expects($this->never())
            ->method('create');

        $this->assertNull($this->model->match($requestMock));
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
}
