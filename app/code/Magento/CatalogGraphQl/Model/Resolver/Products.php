<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Filter;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\SearchFilter;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Model\Layer\Resolver;

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
     * @var SearchFilter
     */
    private $searchFilter;

    /**
     * @var Layer\DataProvider\Filters
     */
    private $filtersDataProvider;

    /**
     * @param Builder $searchCriteriaBuilder
     * @param Search $searchQuery
     * @param Filter $filterQuery
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        Search $searchQuery,
        Filter $filterQuery,
        SearchFilter $searchFilter,
        \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchQuery = $searchQuery;
        $this->filterQuery = $filterQuery;
        $this->searchFilter = $searchFilter;
        $this->filtersDataProvider = $filtersDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $searchCriteria = $this->searchCriteriaBuilder->build($field->getName(), $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);
        if (!isset($args['search']) && !isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'search' or 'filter' input argument is required.")
            );
        } elseif (isset($args['search'])) {
            $layerType = Resolver::CATALOG_LAYER_SEARCH;
            $this->searchFilter->add($args['search'], $searchCriteria);
            $searchResult = $this->searchQuery->getResult($searchCriteria, $info);
        } else {
            $layerType = Resolver::CATALOG_LAYER_CATEGORY;
            $searchResult = $this->filterQuery->getResult($searchCriteria, $info);
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
            'filters' => $this->filtersDataProvider->getData($layerType)
        ];

        return $data;
    }
}
