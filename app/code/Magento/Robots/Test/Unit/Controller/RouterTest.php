<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Robots\Test\Unit\Controller;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFactoryMock;

    /**
     * @var \Magento\Framework\App\Router\ActionList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionListMock;

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $routeConfigMock;

    /**
     * @var \Magento\Robots\Controller\Router
     */
    private $router;

    protected function setUp()
    {
        $this->actionFactoryMock = $this->getMockBuilder(\Magento\Framework\App\ActionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->actionListMock = $this->getMockBuilder(\Magento\Framework\App\Router\ActionList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Route\ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->router = new \Magento\Robots\Controller\Router(
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

        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
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

        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
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
        $actionClassName = '\Magento\Robots\Controller\Index\Index';

        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods([
                'getPathInfo',
                'setModuleName',
                'setControllerName',
                'setActionName',
            ])
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($identifier);
        $requestMock->expects($this->once())
            ->method('setModuleName')
            ->willReturn('robots');
        $requestMock->expects($this->once())
            ->method('setControllerName')
            ->willReturn('index');
        $requestMock->expects($this->once())
            ->method('setActionName')
            ->willReturn('index');

        $this->routeConfigMock->expects($this->once())
            ->method('getModulesByFrontName')
            ->with('robots')
            ->willReturn([$moduleName]);

        $this->actionListMock->expects($this->once())
            ->method('get')
            ->with($moduleName, null, 'index', 'index')
            ->willReturn($actionClassName);

        $actionClassMock = $this->getMockBuilder(\Magento\Robots\Controller\Index\Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->with($actionClassName)
            ->willReturn($actionClassMock);

        $this->assertInstanceOf($actionClassName, $this->router->match($requestMock));
    }
}
