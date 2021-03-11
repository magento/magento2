<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;

/**
 * Class CategoryBasedProductRewriteGeneratorTest
 */
class CategoryBasedProductRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductScopeRewriteGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productScopeRewriteGeneratorMock;

    /**
     * @var CategoryBasedProductRewriteGenerator
     */
    private $generator;

    protected function setUp(): void
    {
        $this->productScopeRewriteGeneratorMock = $this->getMockBuilder(ProductScopeRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generator = new CategoryBasedProductRewriteGenerator(
            $this->productScopeRewriteGeneratorMock
        );
    }

    public function testGenerationWithGlobalScope()
    {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 1;
        $categoryId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(2);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('isGlobalScope')
            ->with($storeId)
            ->willReturn(true);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('generateForGlobalScope')
            ->with([$categoryMock], $productMock, $categoryId)
            ->willReturn($urls);

        $this->assertEquals($urls, $this->generator->generate($productMock, $categoryMock, $categoryId));
    }

    public function testGenerationWithSpecificStore()
    {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 1;
        $categoryId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(2);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('isGlobalScope')
            ->with($storeId)
            ->willReturn(false);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->with($storeId, [$categoryMock], $productMock, $categoryId)
            ->willReturn($urls);

        $this->assertEquals($urls, $this->generator->generate($productMock, $categoryMock, $categoryId));
    }
}
