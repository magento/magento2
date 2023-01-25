<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Robots\Test\Unit\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Robots\Controller\Index\Index;
use Magento\Robots\Controller\Router;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
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
        $this->actionFactoryMock = $this->getMockBuilder(ActionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->actionListMock = $this->getMockBuilder(ActionList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

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

        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPathInfo'])
            ->getMockForAbstractClass();
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

        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPathInfo'])
            ->getMockForAbstractClass();
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

        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPathInfo'])
            ->getMockForAbstractClass();
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

        $actionClassMock = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->with($actionClassName)
            ->willReturn($actionClassMock);

        $this->assertInstanceOf($actionClassName, $this->router->match($requestMock));
    }
}
