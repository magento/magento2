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
     * @param MockObject|\Closure $requestMock
     * @param string $defaultPath
     * @param string $moduleFrontName
     * @param string|null $actionPath
     * @param string|null $actionName
     * @param string|null $moduleName
     */
    public function testMatch(
        MockObject|\Closure $requestMock,
        string $defaultPath,
        string $moduleFrontName,
        ?string $actionPath,
        ?string $actionName,
        ?string $moduleName
    ) {
        $requestMock = $requestMock($this);
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

        $actionMock =  $this->getMock(ActionInterface::class, $actionInstance);
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

    protected function getMockForHttpClass($moduleFrontName, $actionPath, $actionName, $originalPathInfo)
    {
        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->atLeastOnce())->method('getModuleName')->willReturn($moduleFrontName);
        $requestMock->expects($this->atLeastOnce())->method('getControllerName')->willReturn($actionPath);
        $requestMock->expects($this->atLeastOnce())->method('getActionName')->willReturn($actionName);
        if ($moduleFrontName!=null && $moduleFrontName!='') {
            $requestMock->expects($this->atLeastOnce())
                ->method('getPathInfo')
                ->willReturn($moduleFrontName . '/' . $actionPath . '/' . $actionName . '/key/val/key2/val2/');
        }
        else {
            $requestMock->expects($this->atLeastOnce())->method('getPathInfo')->willReturn('');
        }

        if ($originalPathInfo) {
            $requestMock->expects($this->atLeastOnce())->method('getOriginalPathInfo')->willReturn('');
        }
        return $requestMock;
    }

    /**
     * @param string $class
     * @param string $mockClassName
     * @return MockObject
     */
    private function getMock(string $class, string $mockClassName): MockObject
    {
        if (class_exists($mockClassName)) {
            return new $mockClassName();
        }

        $mockBuilder = $this->getMockBuilder($class);
        $mockBuilder->setMockClassName($mockClassName);
        $mockBuilder->disableOriginalConstructor();
        return $mockBuilder->getMockForAbstractClass();
    }

    public static function matchDataProvider(): array
    {
        $moduleFrontName = 'module_front_name';
        $actionPath = 'action_path';
        $actionName = 'action_name';
        $moduleName = 'module_name';

        $requestMock = static fn (self $testCase) => $testCase->getMockForHttpClass(
            $moduleFrontName, $actionPath, $actionName, false
        );

        $emptyRequestMock = static fn (self $testCase) => $testCase->getMockForHttpClass(
            '', '', '', false
        );

        $emptyRequestMock2 = static fn (self $testCase) => $testCase->getMockForHttpClass(
            '', '', '', true
        );

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
     * @param MockObject|\Closure $requestMock
     * @param string $defaultPath
     * @param string $moduleFrontName
     * @param string|null $actionPath
     * @param string|null $actionName
     * @param string|null $moduleName
     */
    public function testMatchEmptyAction(
        MockObject|\Closure $requestMock,
        string $defaultPath,
        string $moduleFrontName,
        ?string $actionPath,
        ?string $actionName,
        ?string $moduleName
    ) {
        $requestMock = $requestMock($this);
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

    protected function getMockForHttpClassTwo($moduleFrontName, $actionPath, $actionName, $bool)
    {
        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->atLeastOnce())->method('getModuleName')->willReturn($moduleFrontName);
        if ($bool) {
            $requestMock->expects($this->atLeastOnce())->method('getControllerName')->willReturn($actionPath);
            $requestMock->expects($this->atLeastOnce())->method('getActionName')->willReturn($actionName);
        }
        if ($moduleFrontName!='') {
            $requestMock->expects($this->atLeastOnce())
                ->method('getPathInfo')
                ->willReturn($moduleFrontName . '/' . $actionPath . '/' . $actionName . '/');
        }
        else {
            $requestMock->expects($this->atLeastOnce())->method('getPathInfo')->willReturn('0');
        }

        return $requestMock;
    }

    public static function matchEmptyActionDataProvider(): array
    {
        $moduleFrontName = 'module_front_name';
        $actionPath = 'action_path';
        $actionName = 'action_name';

        $requestMock1 = static fn (self $testCase) => $testCase->getMockForHttpClassTwo(
            $moduleFrontName, $actionPath, $actionName, true
        );

        $requestMock2 = static fn (self $testCase) => $testCase->getMockForHttpClassTwo(
            $moduleFrontName, $actionPath, $actionName, false
        );

        $requestMock3 = static fn (self $testCase) => $testCase->getMockForHttpClassTwo(
            '', '', '', false
        );

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
