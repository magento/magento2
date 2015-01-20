<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Mvc\Console;

use Zend\Mvc\MvcEvent;

/**
 * Tests Magento\Setup\Mvc\Console\RouteListener
 */
class RouteListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testOnRouteHttpRequest()
    {
        $routeListener = new RouteListener();
        /** @var \Zend\Mvc\MvcEvent|PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $httpRequest = $this->getMockBuilder('Zend\Http\Request')->getMock();
        $mvcEvent->expects($this->any())->method('getRequest')->willReturn($httpRequest);

        // Sending an HttpRequest to console RouteListener should return null
        $this->assertNull($routeListener->onRoute($mvcEvent));
    }

    public function testOnRouteMatch()
    {
        $routeListener = new RouteListener();
        /** @var \Zend\Mvc\MvcEvent|PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->any())->method('getRequest')->willReturn($request);

        $router = $this->getMockBuilder('Zend\Mvc\Router\RouteInterface')->disableOriginalConstructor()->getMock();
        $match = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')->disableOriginalConstructor()->getMock();
        $router->expects($this->any())->method('match')->willReturn($match);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($router);

        // There is a RouteMatch, so RouteListener will return null to trigger default listeners
        $this->assertNull($routeListener->onRoute($mvcEvent));
    }

    public function testOnRouteError()
    {
        $routeListener = new RouteListener();
        /** @var \Zend\Mvc\MvcEvent|PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->any())->method('getRequest')->willReturn($request);

        $router = $this->getMockBuilder('Zend\Mvc\Router\RouteInterface')->disableOriginalConstructor()->getMock();
        $router->expects($this->any())->method('match')->willReturn(null);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($router);

        $request->expects($this->any())->method('getContent')->willReturn([]);

        $application = $this->getMockBuilder('Zend\Mvc\ApplicationInterface')->getMock();
        $serviceManager = $this->getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')->getMock();
        $console = $this->getMockBuilder('Zend\Console\Adapter\AdapterInterface')->getMock();

        $configArray = ['console'=>['router'=>['routes'=>['testAction'=>'test']]]];
        $serviceManager->method('get')->will($this->returnValueMap([['Config', $configArray], ['console', $console]]));

        $application->expects($this->any())->method('getServiceManager')->willReturn($serviceManager);
        $mvcEvent->expects($this->any())->method('getApplication')->willReturn($application);

        // Verify the error is set
        $mvcEvent->expects($this->once())->method('setError');

        // Should always return null
        $this->assertNull($routeListener->onRoute($mvcEvent));
    }

    public function testOnRouteNoError()
    {
        $routeListener = new RouteListener();
        /** @var \Zend\Mvc\MvcEvent|PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->any())->method('getRequest')->willReturn($request);

        $router = $this->getMockBuilder('Zend\Mvc\Router\RouteInterface')->disableOriginalConstructor()->getMock();
        $routeMatch = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')->disableOriginalConstructor()->getMock();
        $router->expects($this->any())->method('match')->willReturn($routeMatch);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($router);

        // Verify the error is not set
        $mvcEvent->expects($this->never())->method('setError');

        // Should always return null
        $this->assertNull($routeListener->onRoute($mvcEvent));
    }


    public function testOnRouteValidationMessage()
    {
        $routeListener = new RouteListener();
        /** @var \Zend\Mvc\MvcEvent|PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->any())->method('getRequest')->willReturn($request);

        $router = $this->getMockBuilder('Zend\Mvc\Router\RouteInterface')->disableOriginalConstructor()->getMock();
        //$routeMatch = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')->disableOriginalConstructor()->getMock();
        $router->expects($this->any())->method('match')->willReturn(null);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($router);

        $request->expects($this->any())->method('getContent')->willReturn(['install']);

        $application = $this->getMockBuilder('Zend\Mvc\ApplicationInterface')->getMock();
        $serviceManager = $this->getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')->getMock();
        $console = $this->getMockBuilder('Zend\Console\Adapter\AdapterInterface')->getMock();

        //        $configArray = ['console'=>['router'=>['routes'=>['testAction'=>['test']]]]];
        $configArray = ['console'=>['router'=>['routes'=>['install'=>['options' => ['route' => 'testRoute']]]]]];
        $serviceManager->method('get')->will($this->returnValueMap([['Config', $configArray], ['console', $console]]));

        $application->expects($this->any())->method('getServiceManager')->willReturn($serviceManager);
        $mvcEvent->expects($this->any())->method('getApplication')->willReturn($application);

        // Verify the error is set
        $mvcEvent->expects($this->once())->method('setError');
        $mvcEvent->expects($this->once())
            ->method('setResult')
            ->with($this->isInstanceOf('Zend\View\Model\ConsoleModel'));

        // Should always return null
        $this->assertNull($routeListener->onRoute($mvcEvent));
    }


    public function testAttach()
    {
        $routeListener = new RouteListener();
        /** @var 'Zend\EventManager\EventManagerInterface|PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMockBuilder('Zend\EventManager\EventManagerInterface')->getMock();
        $eventManager->expects($this->once())
            ->method('attach')
            ->with(
                $this->equalTo(MvcEvent::EVENT_ROUTE),
                $this->contains($routeListener),
                10
            );
        $routeListener->attach($eventManager);
    }
}
