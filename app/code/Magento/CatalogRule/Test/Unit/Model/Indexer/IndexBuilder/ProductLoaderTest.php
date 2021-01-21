<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer\IndexBuilder;

use Magento\CatalogRule\Model\Indexer\IndexBuilder\ProductLoader;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteria;

class ProductLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductLoader
     */
    protected $productLoader;

    /**
     * @var ProductRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductSearchResultsInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productSearchResultsInterface;

    /**
     * @var \Magento\Framework\Api\SearchCriteria|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteria;

    /**
     * @var Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $product;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productSearchResultsInterface = $this->getMockBuilder(ProductSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productLoader = new ProductLoader(
            $this->productRepository,
            $this->searchCriteriaBuilder
        );
    }

    public function testGetProducts()
    {
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteria);
        $this->productRepository->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->willReturn($this->productSearchResultsInterface);
        $iterator = [$this->product];
        $this->productSearchResultsInterface->expects($this->once())
            ->method('getItems')
            ->willReturn($iterator);

        $this->assertSame($iterator, $this->productLoader->getProducts([1]));
    }
}
