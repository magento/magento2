<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Plugin\Webapi\Controller\Rest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\SetSaveRewriteHistory;
use Magento\CatalogUrlRewrite\Plugin\Webapi\Controller\Rest\ProductInputParamsResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\Webapi\Controller\Rest\Router\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ProductInputParamsResolver plugin
 */
class ProductInputParamsResolverTest extends TestCase
{
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
     * @var SetSaveRewriteHistory|MockObject
     */
    private $rewriteHistoryMock;

    /**
     * @var ProductInputParamsResolver
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->route = $this->createMock(Route::class);
        $this->request = $this->createMock(RestRequest::class);
        $this->subject = $this->createMock(InputParamsResolver::class);
        $this->product = $this->createMock(Product::class);
        $this->rewriteHistoryMock = $this->createMock(SetSaveRewriteHistory::class);
    }

    public function testAfterResolveWithProduct()
    {
        $this->subject->expects($this->any())
            ->method('getRoute')
            ->willReturn($this->route);

        $this->result = [false, $this->product, 'test'];

        $this->objectManager = new ObjectManager($this);
        $this->plugin = $this->objectManager->getObject(
            ProductInputParamsResolver::class,
            [
                'rewriteHistory' => $this->rewriteHistoryMock
               ]
        );

        $this->route->expects($this->once())
               ->method('getServiceClass')
               ->willReturn(ProductRepositoryInterface::class);
        $this->route->expects($this->once())
               ->method('getServiceMethod')
               ->willReturn('save');
        $this->rewriteHistoryMock->expects($this->once())
            ->method('execute')
            ->with($this->result, 'product', Product::class)
            ->willReturn($this->result);

        $this->plugin->afterResolve($this->subject, $this->result);
    }
}
