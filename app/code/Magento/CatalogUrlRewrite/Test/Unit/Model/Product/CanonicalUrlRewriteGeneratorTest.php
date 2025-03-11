<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanonicalUrlRewriteGeneratorTest extends TestCase
{
    /** @var CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var ProductUrlPathGenerator|MockObject */
    protected $productUrlPathGenerator;

    /** @var Product|MockObject */
    protected $product;

    /** @var ObjectRegistry|MockObject */
    protected $categoryRegistry;

    /** @var UrlRewriteFactory|MockObject */
    protected $urlRewriteFactory;

    /** @var UrlRewrite|MockObject */
    protected $urlRewrite;

    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRegistry = $this->getMockBuilder(ObjectRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            ProductUrlPathGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->canonicalUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            CanonicalUrlRewriteGenerator::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory
            ]
        );
    }

    public function testGenerate()
    {
        $requestPath = 'simple-product.html';
        $storeId = 10;
        $productId = 'product_id';
        $targetPath = 'catalog/product/view/id/' . $productId;

        $this->product->expects($this->any())->method('getId')->willReturn($productId);
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->willReturn($requestPath);
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->willReturn($targetPath);
        $this->categoryRegistry->expects($this->any())->method('getList')->willReturn([]);

        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($productId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($requestPath)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($targetPath)->willReturnSelf();
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($this->urlRewrite);
        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->product)
        );
    }
}
