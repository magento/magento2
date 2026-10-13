<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Robots\Test\Unit\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Robots\Controller\Index\Index;
use Magento\Robots\Controller\Router;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ActionFactory|MockObject
     */
    private $actionFactoryMock;

    /**
     * @var ActionList|MockObject
     */
    private $actionListMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $routeConfigMock;

    /**
     * @var Router
     */
    private $router;

    protected function setUp(): void
    {
        $this->actionFactoryMock = $this->createPartialMock(ActionFactory::class, ['create']);

        $this->actionListMock = $this->createMock(ActionList::class);

        $this->routeConfigMock = $this->createMock(ConfigInterface::class);

        $this->router = new Router(
            $this->actionFactoryMock,
            $this->actionListMock,
            $this->routeConfigMock
        );
    }

    /**
     * Check case when robots.txt file is not requested
     */
    public function testMatchNoRobotsRequested()
    {
        $identifier = 'test';

        $requestMock = $this->createPartialMockWithReflection(Http::class, ['getPathInfo']);
        $requestMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($identifier);

        $this->assertNull($this->router->match($requestMock));
    }

    /**
     * Check case, when no existed modules in Magento to process 'robots' route
     */
    public function testMatchNoRobotsModules()
    {
        $identifier = 'robots.txt';

        $requestMock = $this->createPartialMockWithReflection(Http::class, ['getPathInfo']);
        $requestMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($identifier);

        $this->routeConfigMock->expects($this->once())
            ->method('getModulesByFrontName')
            ->with('robots')
            ->willReturn([]);

        $this->assertNull($this->router->match($requestMock));
    }

    /**
     * Check the basic flow of match() method
     */
    public function testMatch()
    {
        $identifier = 'robots.txt';
        $moduleName = 'Magento_Robots';
        $actionClassName = Index::class;

        $requestMock = $this->createPartialMockWithReflection(Http::class, ['getPathInfo']);
        $requestMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($identifier);

        $this->routeConfigMock->expects($this->once())
            ->method('getModulesByFrontName')
            ->with('robots')
            ->willReturn([$moduleName]);

        $this->actionListMock->expects($this->once())
            ->method('get')
            ->with($moduleName, null, 'index', 'index')
            ->willReturn($actionClassName);

        $actionClassMock = $this->createMock(Index::class);

        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->with($actionClassName)
            ->willReturn($actionClassMock);

        $this->assertInstanceOf($actionClassName, $this->router->match($requestMock));
    }
}
