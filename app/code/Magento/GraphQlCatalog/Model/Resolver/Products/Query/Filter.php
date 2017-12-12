<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchResult;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchResultFactory;

/**
 * Retrieve filtered product data based off given search criteria in a format that GraphQL can interpret.
 */
class Filter
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var Product
     */
    private $productDataProvider;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchResultFactory $searchResultFactory
     * @param Product $productDataProvider
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchResultFactory $searchResultFactory,
        Product $productDataProvider
    ) {
        $this->productRepository = $productRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->productDataProvider = $productDataProvider;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResult
     */
    public function getResult(SearchCriteriaInterface $searchCriteria)
    {
        $products = $this->productRepository->getList($searchCriteria);
        $productArray = [];
        /** @var ProductInterface $product */
        foreach ($products->getItems() as $product) {
            $productArray[] = $this->productDataProvider->getProduct($product->getSku());
        }
        return $this->searchResultFactory->create($products->getTotalCount(), $productArray);
    }
}
