<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoriesUrlRewriteGeneratorTest extends TestCase
{
    /** @var CategoriesUrlRewriteGenerator */
    protected $categoriesUrlRewriteGenerator;

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
        $this->categoriesUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            CategoriesUrlRewriteGenerator::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory
            ]
        );
    }

    public function testGenerateEmpty()
    {
        $this->categoryRegistry->method('getList')->willReturn([]);

        $this->assertEquals(
            [],
            $this->categoriesUrlRewriteGenerator->generate(1, $this->product, $this->categoryRegistry)
        );
    }

    public function testGenerateCategories()
    {
        $urlPathWithCategory = 'category/simple-product.html';
        $storeId = 10;
        $productId = 'product_id';
        $canonicalUrlPathWithCategory = 'canonical-path-with-category';
        $categoryId = 'category_id';

        $this->product->method('getId')->willReturn($productId);
        $this->productUrlPathGenerator->method('getUrlPathWithSuffix')
            ->willReturn($urlPathWithCategory);
        $this->productUrlPathGenerator->method('getCanonicalUrlPath')
            ->willReturn($canonicalUrlPathWithCategory);
        $category = $this->createMock(Category::class);
        $category->method('getId')->willReturn($categoryId);
        $this->categoryRegistry->method('getList')
            ->willReturn([$category]);

        $this->urlRewrite->method('setStoreId')->with($storeId)->willReturnSelf();
        $this->urlRewrite->method('setEntityId')->with($productId)->willReturnSelf();
        $this->urlRewrite->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite->method('setRequestPath')->with($urlPathWithCategory)->willReturnSelf();
        $this->urlRewrite->method('setTargetPath')->with($canonicalUrlPathWithCategory)->willReturnSelf();
        $this->urlRewrite->method('setMetadata')
            ->with(['category_id' => $categoryId])->willReturnSelf();
        $this->urlRewriteFactory->method('create')->willReturn($this->urlRewrite);

        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $this->categoriesUrlRewriteGenerator->generate($storeId, $this->product, $this->categoryRegistry)
        );
    }
}
