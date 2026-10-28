<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer\IndexBuilder;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\IndexBuilder\ProductLoader;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductLoaderTest extends TestCase
{
    /**
     * @var ProductLoader
     */
    protected $productLoader;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductSearchResultsInterface|MockObject
     */
    private $productSearchResultsInterface;

    /**
     * @var SearchCriteria|MockObject
     */
    private $searchCriteria;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->productSearchResultsInterface = $this->createMock(ProductSearchResultsInterface::class);
        $this->searchCriteria = $this->createMock(SearchCriteria::class);
        $this->product = $this->createMock(Product::class);

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
