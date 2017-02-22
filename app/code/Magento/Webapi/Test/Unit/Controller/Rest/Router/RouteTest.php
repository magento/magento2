<?php
/**
 * Test Rest router route.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Controller\Rest\Router;

use \Magento\Webapi\Controller\Rest\Router\Route;

use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->setMethods(['getPathInfo'])
            ->getMockForAbstractClass();
    }

    /**
     * Test setServiceName and getServiceName methods
     *
     * @return void
     */
    public function testResourceName()
    {
        /** @var Route $model */
        $model = $this->objectManager->getObject(
            'Magento\Webapi\Controller\Rest\Router\Route',
            ['route' => '/V1/one']
        );

        /** Assert that new object has no Resource name set. */
        $this->assertNull($model->getServiceClass(), 'New object has a set Resource name.');
        /** Set Resource name. */
        $resourceName = 'Resource name';
        $model->setServiceClass($resourceName);
        /** Assert that Resource name was set. */
        $this->assertEquals($resourceName, $model->getServiceClass(), 'Resource name is wrong.');
    }

    /**
     * @param string $route
     * @param string $path
     * @param array|bool $params
     * @return void
     * @dataProvider dataProviderRoutes
     */
    public function testRoute($route, $path, $params)
    {
        /** @var Route $model */
        $model = $this->objectManager->getObject(
            'Magento\Webapi\Controller\Rest\Router\Route',
            ['route' => $route]
        );

        $this->request->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($path);

        $match = $model->match($this->request);
        $this->assertEquals($params, $match);
    }

    /**
     * @return array
     */
    public function dataProviderRoutes()
    {
        return [
            // Success
            ['/V1/one', '/V1/one', []],
            ['/V1/one/:twoValue', '/V1/one/2', ['twoValue' => 2]],
            ['/V1/one/two', '/V1/one/two', []],
            ['/V1/one/two/:threeValue', '/V1/one/two/3', ['threeValue' => 3]],
            ['/V1/one/:twoValue/three', '/V1/one/2/three', ['twoValue' => 2]],
            ['/V1/one/:twoValue/three/:fourValue', '/V1/one/2/three/4', ['twoValue' => 2, 'fourValue' => 4]],
            ['/V1/one/:twoValue/three/four', '/V1/one/2/three/four', ['twoValue' => 2]],
            ['/V1/one/two/:threeValue/four/:fiveValue', '/V1/one/two/3/four/5', ['threeValue' => 3, 'fiveValue' => 5]],

            ['/v1/One', '/v1/One', []],

            ['/v1/oNe/:TwoValue', '/v1/oNe/2', ['TwoValue' => 2]],
            ['/v1/onE/:twovalue', '/v1/onE/2', ['twovalue' => 2]],

            ['/V1/one-one', '/V1/one-one', []],
            ['/V1/one-one/:twoValue', '/V1/one-one/2', ['twoValue' => 2]],
            ['/V1/one_one/:two-value', '/V1/one_one/2', ['two-value' => 2]],
            ['/V1/one-one/:two_value', '/V1/one-one/2', ['two_value' => 2]],

            // Error
            ['/v1/oNe', '/V1/one', false],
            ['/v1/onE', '/V1/oNe', false],
            ['/v1/One/:twoValue', '/V1/one/2', false],
            ['/V1/one', '/V1/two', false],
            ['/V1/one/:twoValue', '/V1/one', false],
            ['/V1/one/two', '/V1/one', false],
            ['/V1/one/two', '/V1/one/two/three', false],
            ['/V1/one/:twoValue/three', '/V1/one/two/3', false],
        ];
    }
}
