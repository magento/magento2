<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Plugin\Webapi\Controller\Rest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Plugin\Webapi\Controller\Rest\InputParamsResolver as InputParamsResolverPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\Webapi\Controller\Rest\Router\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for InputParamsResolver plugin
 */
class InputParamsResolverTest extends TestCase
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
     * @var InputParamsResolver|MockObject
     */
    private $subject;

    /**
     * @var RestRequest|MockObject
     */
    private $request;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Route|MockObject
     */
    private $route;

    /**
     * @var InputParamsResolverPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->saveRewritesHistory = 'save_rewrites_history';
        $this->requestBodyParams = [
            'product' => [
                'sku' => 'test',
                'custom_attributes' => [
                    ['attribute_code' => $this->saveRewritesHistory, 'value' => 1]
                ]
            ]
        ];

        $this->route = $this->createPartialMock(Route::class, ['getServiceMethod', 'getServiceClass']);
        $this->request = $this->createPartialMock(RestRequest::class, ['getBodyParams']);
        $this->request->expects($this->any())->method('getBodyParams')->willReturn($this->requestBodyParams);
        $this->subject = $this->createPartialMock(InputParamsResolver::class, ['getRoute']);
        $this->subject->expects($this->any())->method('getRoute')->willReturn($this->route);
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
        $this->route->expects($this->once())
            ->method('getServiceClass')
            ->willReturn(ProductRepositoryInterface::class);
        $this->route->expects($this->once())
            ->method('getServiceMethod')
            ->willReturn('save');
        $this->product->expects($this->once())
            ->method('setData')
            ->with($this->saveRewritesHistory, true);

        $this->plugin->afterResolve($this->subject, $this->result);
    }
}
