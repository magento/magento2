<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;

/**
 * Retrieve filtered product data based off given search criteria in a format that GraphQL can interpret.
 */
class Filter
{
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product
     */
    private $productDataProvider;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * FieldSelection
     */
    private $fieldSelection;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param Product $productDataProvider
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param FieldSelection $fieldSelection
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Product $productDataProvider,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        FieldSelection $fieldSelection
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->productDataProvider = $productDataProvider;
        $this->layerResolver = $layerResolver;
        $this->fieldSelection = $fieldSelection;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param ResolveInfo $info
     * @param bool $isSearch
     * @return SearchResult
     */
    public function getResult(
        SearchCriteriaInterface $searchCriteria,
        ResolveInfo $info,
        bool $isSearch = false
    ): SearchResult {
        $fields = $this->fieldSelection->getProductsFieldSelection($info);
        $products = $this->productDataProvider->getList($searchCriteria, $fields, $isSearch);
        $productArray = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products->getItems() as $product) {
            $productArray[$product->getId()] = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }

        return $this->searchResultFactory->create(
            [
                'totalCount' => $products->getTotalCount(),
                'productsSearchResult' => $productArray
            ]
        );
    }
}
