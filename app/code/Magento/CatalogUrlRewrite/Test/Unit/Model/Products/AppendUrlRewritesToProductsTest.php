<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Products;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\GetProductUrlRewriteDataByStore;
use Magento\CatalogUrlRewrite\Model\Products\AppendUrlRewritesToProducts;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AppendUrlRewritesToProductsTest extends TestCase
{
    /**
     * @var ProductUrlRewriteGenerator|MockObject
     */
    private ProductUrlRewriteGenerator $productUrlRewriteGenerator;

    /**
     * @var StoreViewService|MockObject
     */
    private StoreViewService $storeViewService;

    /**
     * @var ProductUrlPathGenerator|MockObject
     */
    private ProductUrlPathGenerator $productUrlPathGenerator;

    /**
     * @var UrlPersistInterface|MockObject
     */
    private UrlPersistInterface $urlPersist;

    /**
     * @var GetProductUrlRewriteDataByStore|MockObject
     */
    private GetProductUrlRewriteDataByStore $getDataByStore;

    /**
     * @var AppendUrlRewritesToProducts
     */
    private AppendUrlRewritesToProducts $append;

    protected function setUp(): void
    {
        $this->productUrlRewriteGenerator = $this->createMock(ProductUrlRewriteGenerator::class);
        $this->storeViewService = $this->createMock(StoreViewService::class);
        $this->productUrlPathGenerator = $this->createMock(ProductUrlPathGenerator::class);
        $this->urlPersist = $this->createMock(UrlPersistInterface::class);
        $this->getDataByStore = $this->createMock(GetProductUrlRewriteDataByStore::class);

        $this->append = new AppendUrlRewritesToProducts(
            $this->productUrlRewriteGenerator,
            $this->storeViewService,
            $this->productUrlPathGenerator,
            $this->urlPersist,
            $this->getDataByStore
        );
        parent::setUp();
    }

    /**
     * @return void
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function testSaveProductUrlRewrites(): void
    {
        $rewrites = ['test'];
        $this->urlPersist->expects($this->once())->method('replace')->with($rewrites);
        $this->append->saveProductUrlRewrites($rewrites);
    }

    /**
     * @return void
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function testGetProductUrlRewrites(): void
    {
        $storeId = $productId = 1;
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['unsUrlPath', 'setUrlPath'])
            ->onlyMethods(['getStoreId', 'getId', 'getStoreIds'])
            ->getMock();
        $product->expects($this->any())->method('getStoreId')->willReturn(0);
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $product->expects($this->any())->method('getStoreIds')->willReturn([$storeId]);
        $product->expects($this->once())->method('unsUrlPath');
        $product->expects($this->once())->method('setUrlPath');

        $this->productUrlPathGenerator->expects($this->once())->method('getUrlPath');
        $this->productUrlRewriteGenerator->expects($this->once())->method('generate')->willReturn([]);
        $this->getDataByStore->expects($this->once())->method('clearProductUrlRewriteDataCache');
        $this->urlPersist->expects($this->once())->method('replace');

        $this->storeViewService->expects($this->once())
            ->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->with($storeId, $productId, Product::ENTITY)
            ->willReturn(false);

        $this->append->execute([$product], [$storeId]);
    }
}
