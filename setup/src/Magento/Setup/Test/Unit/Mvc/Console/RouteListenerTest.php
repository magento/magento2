<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Mvc\Console;

use \Magento\Setup\Mvc\Console\RouteListener;

use Zend\Mvc\MvcEvent;

/**
 * Tests Magento\Setup\Mvc\Console\RouteListener
 */
class RouteListenerTest extends \PHPUnit_Framework_TestCase
{

    /** @var RouteListener */
    private $routeListener;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $router;

    protected function setUp()
    {
        $this->routeListener = new RouteListener();
        $this->request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $this->router = $this->getMockBuilder('Zend\Mvc\Router\RouteInterface')
            ->disableOriginalConstructor()->getMock();
    }

    public function testOnRouteHttpRequest()
    {
        /** @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $httpRequest = $this->getMockBuilder('Zend\Http\Request')->getMock();
        $mvcEvent->expects($this->any())->method('getRequest')->willReturn($httpRequest);

        // Sending an HttpRequest to console RouteListener should return null
        $this->assertNull($this->routeListener->onRoute($mvcEvent));
    }

    public function testOnRouteMatch()
    {
        $mvcEvent = $this->prepareEvent();

        $match = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')->disableOriginalConstructor()->getMock();
        $this->router->expects($this->any())->method('match')->willReturn($match);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($this->router);

        // There is a RouteMatch, so RouteListener will return null to trigger default listeners
        $this->assertNull($this->routeListener->onRoute($mvcEvent));
    }

    public function testOnRouteError()
    {
        $mvcEvent = $this->prepareEvent();

        $this->router->expects($this->any())->method('match')->willReturn(null);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($this->router);

        $this->request->expects($this->any())->method('getContent')->willReturn([]);
        $this->prepareOnRoute($mvcEvent, ['console' => ['router' => ['routes' => ['testAction' => 'test']]]]);

        // Verify the error is set
        $mvcEvent->expects($this->once())->method('setError');

        // Should always return null
        $this->assertNull($this->routeListener->onRoute($mvcEvent));
    }

    public function testOnRouteNoError()
    {
        $mvcEvent = $this->prepareEvent();

        $routeMatch = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')->disableOriginalConstructor()->getMock();
        $this->router->expects($this->any())->method('match')->willReturn($routeMatch);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($this->router);

        // Verify the error is not set
        $mvcEvent->expects($this->never())->method('setError');

        // Should always return null
        $this->assertNull($this->routeListener->onRoute($mvcEvent));
    }


    public function testOnRouteValidationMessage()
    {
        $mvcEvent = $this->prepareEvent();

        $this->router->expects($this->any())->method('match')->willReturn(null);
        $mvcEvent->expects($this->any())->method('getRouter')->willReturn($this->router);

        $this->request->expects($this->any())->method('getContent')->willReturn(['install']);

        $this->prepareOnRoute(
            $mvcEvent,
            ['console' => ['router' => ['routes' => ['install' => ['options' => ['route' => 'testRoute']]]]]]
        );

        // Verify the error is set
        $mvcEvent->expects($this->once())->method('setError');
        $mvcEvent->expects($this->once())
            ->method('setResult')
            ->with($this->isInstanceOf('Zend\View\Model\ConsoleModel'));

        // Should always return null
        $this->assertNull($this->routeListener->onRoute($mvcEvent));
    }


    public function testAttach()
    {
        /** @var \Zend\EventManager\EventManagerInterface|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMockBuilder('Zend\EventManager\EventManagerInterface')->getMock();
        $eventManager->expects($this->once())
            ->method('attach')
            ->with(
                $this->equalTo(MvcEvent::EVENT_ROUTE),
                $this->contains($this->routeListener),
                10
            );
        $this->routeListener->attach($eventManager);
    }

    /**
     * Create a mock MVC event with a console mock console request.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|MvcEvent
     */
    private function prepareEvent()
    {
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->any())->method('getRequest')->willReturn($this->request);
        return $mvcEvent;
    }

    /**
     * Add a mock application, service manager and console adapter.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $mvcEvent
     * @param $configArray
     */
    private function prepareOnRoute($mvcEvent, $configArray)
    {
        $application = $this->getMockBuilder('Zend\Mvc\ApplicationInterface')->getMock();
        $serviceManager = $this->getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')->getMock();
        $console = $this->getMockBuilder('Zend\Console\Adapter\AdapterInterface')->getMock();

        $serviceManager
            ->expects($this->any())
            ->method('get')->will($this->returnValueMap([['Config', $configArray], ['console', $console]]));

        $application->expects($this->any())->method('getServiceManager')->willReturn($serviceManager);
        $mvcEvent->expects($this->any())->method('getApplication')->willReturn($application);
    }
}
