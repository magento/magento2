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
     * @param string|null $actionPath
     * @param string|null $actionName
     * @param string|null $moduleName
     */
    public function testMatch(
        MockObject $requestMock,
        string $defaultPath,
        string $moduleFrontName,
        ?string $actionPath,
        ?string $actionName,
        ?string $moduleName
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

        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->atLeastOnce())->method('getModuleName')->willReturn($moduleFrontName);
        $requestMock->expects($this->atLeastOnce())->method('getControllerName')->willReturn($actionPath);
        $requestMock->expects($this->atLeastOnce())->method('getActionName')->willReturn($actionName);
        $requestMock->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturn($moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/');

        $emptyRequestMock = $this->createMock(Http::class);
        $emptyRequestMock->expects($this->atLeastOnce())->method('getModuleName')->willReturn('');
        $emptyRequestMock->expects($this->atLeastOnce())->method('getControllerName')->willReturn('');
        $emptyRequestMock->expects($this->atLeastOnce())->method('getActionName')->willReturn('');
        $emptyRequestMock->expects($this->atLeastOnce())->method('getPathInfo')->willReturn('');

        $emptyRequestMock2 = $this->createMock(Http::class);
        $emptyRequestMock2->expects($this->atLeastOnce())->method('getModuleName')->willReturn('');
        $emptyRequestMock2->expects($this->atLeastOnce())->method('getControllerName')->willReturn('');
        $emptyRequestMock2->expects($this->atLeastOnce())->method('getActionName')->willReturn('');
        $emptyRequestMock2->expects($this->atLeastOnce())->method('getPathInfo')->willReturn('');
        $emptyRequestMock2->expects($this->atLeastOnce())->method('getOriginalPathInfo')->willReturn('');

        return [
            [
                $requestMock,
                'val1/val2/val3/',
                $moduleFrontName,
                $actionPath,
                $actionName,
                $moduleName
            ],
            [
                $emptyRequestMock,
                $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/',
                $moduleFrontName,
                $actionPath,
                $actionName,
                $moduleName
            ],
            [
                $emptyRequestMock2,
                '',
                $moduleFrontName,
                $actionPath,
                $actionName,
                $moduleName
            ],
        ];
    }

    /**
     * @dataProvider matchEmptyActionDataProvider
     * @param MockObject|Http $requestMock
     * @param string $defaultPath
     * @param string $moduleFrontName
     * @param string|null $actionPath
     * @param string|null $actionName
     * @param string|null $moduleName
     */
    public function testMatchEmptyAction(
        MockObject $requestMock,
        string $defaultPath,
        string $moduleFrontName,
        ?string $actionPath,
        ?string $actionName,
        ?string $moduleName
    ) {
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
            ->willReturn($moduleName ? [$moduleName] : []);
        $this->actionFactoryMock->expects($this->never())
            ->method('create');

        $this->assertNull($this->model->match($requestMock));
    }

    public function matchEmptyActionDataProvider(): array
    {
        $moduleFrontName = 'module_front_name';
        $actionPath = 'action_path';
        $actionName = 'action_name';

        $requestMock1 = $this->createMock(Http::class);
        $requestMock1->expects($this->atLeastOnce())->method('getModuleName')->willReturn($moduleFrontName);
        $requestMock1->expects($this->atLeastOnce())->method('getControllerName')->willReturn($actionPath);
        $requestMock1->expects($this->atLeastOnce())->method('getActionName')->willReturn($actionName);
        $requestMock1->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturn($moduleFrontName . '/' . $actionPath . '/' . $actionName . '/');

        $requestMock2 = $this->createMock(Http::class);
        $requestMock2->expects($this->atLeastOnce())->method('getModuleName')->willReturn($moduleFrontName);
        $requestMock2->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturn($moduleFrontName . '/' . $actionPath . '/' . $actionName . '/');

        $requestMock3 = $this->createMock(Http::class);
        $requestMock3->expects($this->atLeastOnce())->method('getModuleName')->willReturn('');
        $requestMock3->expects($this->atLeastOnce())->method('getPathInfo')->willReturn('0');

        return [
            [
                $requestMock1,
                '',
                $moduleFrontName,
                $actionPath,
                $actionName,
                'module_name',
            ],
            [
                $requestMock2,
                '',
                $moduleFrontName,
                $actionPath,
                $actionName,
                null,
            ],
            [
                $requestMock3,
                $moduleFrontName . '/' . $actionPath . '/' . $actionName . '/',
                '0',
                null,
                null,
                null
            ],
        ];
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
