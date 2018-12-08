<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Plugin\Webapi\Controller\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\CatalogUrlRewrite\Plugin\Webapi\Controller\Rest\InputParamsResolver as InputParamsResolverPlugin;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Catalog\Model\Product;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Catalog\Api\ProductRepositoryInterface;
<<<<<<< HEAD
use PHPUnit_Framework_MockObject_MockObject as MockObject;
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

/**
 * Unit test for InputParamsResolver plugin
 */
class InputParamsResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $saveRewritesHistory;

    /**
     * @var array
     */
    private $requestBodyParams;

    /**
     * @var array
     */
    private $result;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
<<<<<<< HEAD
     * @var InputParamsResolver|MockObject
=======
     * @var InputParamsResolver|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $subject;

    /**
<<<<<<< HEAD
     * @var RestRequest|MockObject
=======
     * @var RestRequest|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $request;

    /**
<<<<<<< HEAD
     * @var Product|MockObject
=======
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $product;

    /**
<<<<<<< HEAD
     * @var Route|MockObject
=======
     * @var Route|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $route;

    /**
     * @var InputParamsResolverPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->saveRewritesHistory = 'save_rewrites_history';
        $this->requestBodyParams = [
            'product' => [
                'sku' => 'test',
                'custom_attributes' => [
<<<<<<< HEAD
                    [
                        'attribute_code' => $this->saveRewritesHistory,
                        'value' => 1
                    ]
=======
                    ['attribute_code' => $this->saveRewritesHistory, 'value' => 1]
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
                ]
            ]
        ];

        $this->route = $this->createPartialMock(Route::class, ['getServiceMethod', 'getServiceClass']);
        $this->request = $this->createPartialMock(RestRequest::class, ['getBodyParams']);
<<<<<<< HEAD
        $this->request->method('getBodyParams')
            ->willReturn($this->requestBodyParams);
        $this->subject = $this->createPartialMock(InputParamsResolver::class, ['getRoute']);
        $this->subject->method('getRoute')
            ->willReturn($this->route);
=======
        $this->request->expects($this->any())->method('getBodyParams')->willReturn($this->requestBodyParams);
        $this->subject = $this->createPartialMock(InputParamsResolver::class, ['getRoute']);
        $this->subject->expects($this->any())->method('getRoute')->willReturn($this->route);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $this->product = $this->createPartialMock(Product::class, ['setData']);

        $this->result = [false, $this->product, 'test'];

        $this->objectManager = new ObjectManager($this);
        $this->plugin = $this->objectManager->getObject(
            InputParamsResolverPlugin::class,
            [
                'request' => $this->request
            ]
        );
    }

    public function testAfterResolve()
    {
<<<<<<< HEAD
        $this->route->method('getServiceClass')
            ->willReturn(ProductRepositoryInterface::class);
        $this->route->method('getServiceMethod')
=======
        $this->route->expects($this->once())
            ->method('getServiceClass')
            ->willReturn(ProductRepositoryInterface::class);
        $this->route->expects($this->once())
            ->method('getServiceMethod')
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            ->willReturn('save');
        $this->product->expects($this->once())
            ->method('setData')
            ->with($this->saveRewritesHistory, true);

        $this->plugin->afterResolve($this->subject, $this->result);
    }
}
