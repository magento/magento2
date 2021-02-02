<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CategoriesUrlRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator */
    protected $categoriesUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit\Framework\MockObject\MockObject */
    protected $productUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject */
    protected $product;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $categoryRegistry;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlRewriteFactory;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlRewrite;

    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryRegistry = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Model\ObjectRegistry::class)
            ->disableOriginalConstructor()->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->categoriesUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator::class,
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory
            ]
        );
    }

    public function testGenerateEmpty()
    {
        $this->categoryRegistry->expects($this->any())->method('getList')->willReturn([]);

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

        $this->product->expects($this->any())->method('getId')->willReturn($productId);
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->willReturn($urlPathWithCategory);
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->willReturn($canonicalUrlPathWithCategory);
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getId')->willReturn($categoryId);
        $this->categoryRegistry->expects($this->any())->method('getList')
            ->willReturn([$category]);

        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)
            ->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($productId)
            ->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($urlPathWithCategory)
            ->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($canonicalUrlPathWithCategory)
            ->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setMetadata')
            ->with(['category_id' => $categoryId])->willReturnSelf();
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($this->urlRewrite);

        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $this->categoriesUrlRewriteGenerator->generate($storeId, $this->product, $this->categoryRegistry)
        );
    }
}
