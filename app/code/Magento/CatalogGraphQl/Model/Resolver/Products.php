<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Filter;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

/**
 * Products field resolver, used for GraphQL request processing.
 */
class Products implements ResolverInterface
{
    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Search
     */
    private $searchQuery;

    /**
     * @var Filter
     */
    private $filterQuery;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Layer\DataProvider\LayerFilters
     */
    private $layerFiltersDataProvider;
    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * @param Builder $searchCriteriaBuilder
     * @param Search $searchQuery
     * @param Filter $filterQuery
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        Search $searchQuery,
        Filter $filterQuery,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\LayerFilters $layerFiltersDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchQuery = $searchQuery;
        $this->filterQuery = $filterQuery;
        $this->layerFiltersDataProvider = $layerFiltersDataProvider;
        $this->layerResolver = $layerResolver;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ) : ?Value {
        $searchCriteria = $this->searchCriteriaBuilder->build($args);
        if (!isset($args['search']) && !isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'search' or 'filter' input argument is required.")
            );
        } elseif (isset($args['search'])) {
            $this->layerResolver->create(\Magento\CatalogGraphQl\Model\Layer\Search::LAYER_GRAPHQL_SEARCH);
            $searchResult = $this->searchQuery->getResult($searchCriteria);
            $this->layerResolver->get();
            $layerFilters = $this->layerFiltersDataProvider->getFilters(\Magento\CatalogGraphQl\Model\Layer\Search::LAYER_GRAPHQL_SEARCH);
        } else {
            $this->layerResolver->create(\Magento\CatalogGraphQl\Model\Layer\Category::LAYER_GRAPHQL_CATEGORY);
            $this->layerResolver->get();
            $searchResult = $this->filterQuery->getResult($searchCriteria);
            $layerFilters = $this->layerFiltersDataProvider->getFilters(\Magento\CatalogGraphQl\Model\Layer\Category::LAYER_GRAPHQL_CATEGORY);
        }
        //possible division by 0
        if ($searchCriteria->getPageSize()) {
            $maxPages = ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize());
        } else {
            $maxPages = 0;
        }

        $currentPage = $searchCriteria->getCurrentPage();
        if ($searchCriteria->getCurrentPage() > $maxPages && $searchResult->getTotalCount() > 0) {
            $currentPage = new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the number of pages available.',
                    [$maxPages]
                )
            );
        }

        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $searchResult->getProductsSearchResult(),
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $currentPage
            ],
            'filters' => $layerFilters,
        ];

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
