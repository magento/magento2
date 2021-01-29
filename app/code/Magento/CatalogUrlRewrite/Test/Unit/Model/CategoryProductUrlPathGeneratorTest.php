<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class test generate product url path
 */
class CategoryProductUrlPathGeneratorTest extends TestCase
{
    /**
     * @var ProductScopeRewriteGenerator|MockObject
     */
    private $productScopeRewriteGeneratorMock;

    /**
     * @var CategoryProductUrlPathGenerator
     */
    private $generator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productScopeRewriteGeneratorMock = $this->getMockBuilder(ProductScopeRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generator = new CategoryProductUrlPathGenerator(
            $this->productScopeRewriteGeneratorMock
        );
    }

    /**
     * Test to generate product url rewrites based on all product categories on global scope
     */
    public function testGenerationWithGlobalScope()
    {
        /** @var Collection|MockObject $categoryCollectionMock */
        $categoryCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Product|MockObject $productMock */
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
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($categoryCollectionMock);
        $categoryCollectionMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $categoryCollectionMock->expects($this->atLeastOnce())
            ->method('addAttributeToSelect')
            ->willReturnSelf();

        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('isGlobalScope')
            ->with($storeId)
            ->willReturn(true);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('generateForGlobalScope')
            ->with($categoryCollectionMock, $productMock, $categoryId)
            ->willReturn($urls);

        $this->assertEquals($urls, $this->generator->generate($productMock, $categoryId));
    }

    /**
     * Test to generate product url rewrites based on all product categories on specific store
     */
    public function testGenerationWithSpecificStore()
    {
        /** @var Collection|MockObject $categoryCollectionMock */
        $categoryCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Product|MockObject $productMock */
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
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($categoryCollectionMock);
        $categoryCollectionMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $categoryCollectionMock->expects($this->atLeastOnce())
            ->method('addAttributeToSelect')
            ->willReturnSelf();

        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('isGlobalScope')
            ->with($storeId)
            ->willReturn(false);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->with($storeId, $categoryCollectionMock, $productMock, $categoryId)
            ->willReturn($urls);

        $this->assertEquals($urls, $this->generator->generate($productMock, $categoryId));
    }
}
