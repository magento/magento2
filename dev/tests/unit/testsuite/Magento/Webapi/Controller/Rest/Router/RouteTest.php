<?php
/**
 * Test Rest router route.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest\Router;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Rest\Router\Route */
    protected $_restRoute;

    protected function setUp()
    {
        /** Init SUT. */
        $this->_restRoute = new \Magento\Webapi\Controller\Rest\Router\Route('route');
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_restRoute);
        parent::tearDown();
    }

    /**
     * Test setServiceName and getServiceName methods.
     */
    public function testResourceName()
    {
        /** Assert that new object has no Resource name set. */
        $this->assertNull($this->_restRoute->getServiceClass(), 'New object has a set Resource name.');
        /** Set Resource name. */
        $resourceName = 'Resource name';
        $this->_restRoute->setServiceClass($resourceName);
        /** Assert that Resource name was set. */
        $this->assertEquals($resourceName, $this->_restRoute->getServiceClass(), 'Resource name is wrong.');
    }

    public function testMatch()
    {
        $areaName = 'rest';
        $testApi = 'test_api';
        $route = new \Magento\Webapi\Controller\Rest\Router\Route("{$areaName}/:{$testApi}");

        $testUri = "{$areaName}/{$testApi}";
        $request = new \Zend_Controller_Request_Http();
        $request->setRequestUri($testUri);

        $match = $route->match($request);
        $this->assertEquals($testApi, $match[$testApi], 'Rest route did not match.');
    }
}
