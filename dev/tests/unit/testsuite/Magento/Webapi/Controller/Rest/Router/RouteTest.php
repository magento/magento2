<?php
/**
 * Test Rest router route.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
