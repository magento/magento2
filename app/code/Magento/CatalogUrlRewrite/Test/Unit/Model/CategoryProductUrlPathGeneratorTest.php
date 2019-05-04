<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\Catalog\Model\ResourceModel\Category\Collection;

/**
 * Class CategoryProductUrlPathGeneratorTest
 */
class CategoryProductUrlPathGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductScopeRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productScopeRewriteGeneratorMock;

    /**
     * @var CategoryProductUrlPathGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->productScopeRewriteGeneratorMock = $this->getMockBuilder(ProductScopeRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generator = new CategoryProductUrlPathGenerator(
            $this->productScopeRewriteGeneratorMock
        );
    }

    public function testGenerationWithGlobalScope()
    {
        $categoryCollectionMock = $this->getMockBuilder(Collection::class)
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
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($categoryCollectionMock);
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

    public function testGenerationWithSpecificStore()
    {
        $categoryCollectionMock = $this->getMockBuilder(Collection::class)
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
        $productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($categoryCollectionMock);
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
