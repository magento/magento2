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
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\GraphQl\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Filter;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\Framework\Registry;

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
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\CatalogGraphQl\Model\Resolver\Layer\FilterableAttributesListFactory
     */
    private $filterableAttributesListFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\FilterListFactory
     */
    private $filterListFactory;

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
        ValueFactory $valueFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterListFactory $filterListFactory,
        \Magento\CatalogGraphQl\Model\Resolver\Layer\FilterableAttributesListFactory $filterableAttributesListFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchQuery = $searchQuery;
        $this->filterQuery = $filterQuery;
        $this->layerResolver = $layerResolver;
        $this->filterableAttributesListFactory = $filterableAttributesListFactory;
        $this->filterListFactory = $filterListFactory;
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
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
            $filterableAttributesList = $this->filterableAttributesListFactory->create(
                Resolver::CATALOG_LAYER_SEARCH
            );
            $searchResult = $this->searchQuery->getResult($searchCriteria, $info);
        } else {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            $filterableAttributesList = $this->filterableAttributesListFactory->create(
                Resolver::CATALOG_LAYER_CATEGORY
            );
            $searchResult = $this->filterQuery->getResult($searchCriteria, $info);
        }
        //possible division by 0
        if ($searchCriteria->getPageSize()) {
            $maxPages = ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize());
        } else {
            $maxPages = 0;
        }
        /////////////////
        $filterList = $this->filterListFactory->create(
            [
                'filterableAttributes' => $filterableAttributesList
            ]
        );
        /** @var Registry $registry */
        $registry = \Magento\Framework\App\ObjectManager::getInstance()->get(Registry::class);
        $registry->register('graphql_search_criteria', $searchCriteria);
        $filters = $filterList->getFilters($this->layerResolver->get());
        $filtersArray = [];
        /** @var AbstractFilter $filter */
        foreach ($filters as $filter) {
            if ($filter->getItemsCount()) {
                $filterGroup = [
                    'name' => (string)$filter->getName(),
                    'filter_items_count' => $filter->getItemsCount(),
                    'request_var' => $filter->getRequestVar(),
                ];
                /** @var \Magento\Catalog\Model\Layer\Filter\Item $filterItem */
                foreach ($filter->getItems() as $filterItem) {
                    $filterGroup['filter_items'][] = [
                        'label' => (string)$filterItem->getLabel(),
                        'value_string' => $filterItem->getValueString(),
                        'items_count' => $filterItem->getCount(),
                    ];
                }
                $filtersArray[] = $filterGroup;
            }
        }
        /////////////////
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
            'filters' => $filtersArray
        ];

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
