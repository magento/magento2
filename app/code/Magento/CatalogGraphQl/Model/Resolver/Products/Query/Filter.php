<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Query\PostFetchProcessorInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var PostFetchProcessorInterface[]
     */
    private $postProcessors;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchResultFactory $searchResultFactory
     * @param Product $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FormatterInterface $formatter
     * @param PostFetchProcessorInterface[] $postProcessors
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchResultFactory $searchResultFactory,
        Product $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FormatterInterface $formatter,
        array $postProcessors = []
    ) {
        $this->productRepository = $productRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->postProcessors = $postProcessors;
        $this->formatter = $formatter;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResult
     */
    public function getResult(SearchCriteriaInterface $searchCriteria)
    {
        $realPageSize = $searchCriteria->getPageSize();
        $realCurrentPage = $searchCriteria->getCurrentPage();
        // Current page must be set to 0 and page size to max for search to grab all ID's as temporary workaround for
        // inaccurate search
        $searchCriteria->setPageSize(PHP_INT_MAX);
        $searchCriteria->setCurrentPage(1);
        $products = $this->productDataProvider->getList($searchCriteria);
        $productArray = [];
        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);
        $paginatedProducts = $this->paginateList($products->getItems(), $searchCriteria);
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($paginatedProducts as $product) {
            $productArray[] = $this->formatter->format($product);
        }

        foreach ($this->postProcessors as $postProcessor) {
            $productArray = $postProcessor->process($productArray);
        }

        return $this->searchResultFactory->create($products->getTotalCount(), $productArray);
    }

    /**
     * Paginates array of Ids pulled back in search based off search criteria and total count.
     *
     * @param array $ids
     * @param SearchCriteriaInterface $searchCriteria
     * @return int[]
     */
    private function paginateList(array $ids, SearchCriteriaInterface $searchCriteria)
    {
        $length = $searchCriteria->getPageSize();
        // Search starts pages from 0
        $offset = $length * ($searchCriteria->getCurrentPage() - 1);
        return array_slice($ids, $offset, $length);
    }
}
