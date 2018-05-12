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
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
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
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Layer\DataProvider\Filters
     */
    private $filtersDataProvider;
    
    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Builder $searchCriteriaBuilder
     * @param Search $searchQuery
     * @param Filter $filterQuery
     * @param ValueFactory $valueFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        Search $searchQuery,
        Filter $filterQuery,
        SearchFilter $searchFilter,
        ValueFactory $valueFactory,
        \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchQuery = $searchQuery;
        $this->filterQuery = $filterQuery;
        $this->searchFilter = $searchFilter;
        $this->valueFactory = $valueFactory;
        $this->filtersDataProvider = $filtersDataProvider;
        $this->catalogConfig = $catalogConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {
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

        $options = $this->catalogConfig->getAttributeUsedForSortByArray();
        
        $sortFields = [
            'default' => $this->catalogConfig->getProductListDefaultSortBy($this->storeManager->getStore()->getId()),
            'options' => []
        ];
        
        $sortFields['options'][] = ['key' => 'position', 'label' => 'Position'];
        foreach ($this->catalogConfig->getAttributesUsedForSortBy() as $attribute) {
            $sortFields['options'][] = ['key' => $attribute->getAttributeCode(), 'label' => $attribute->getStoreLabel()];
        }
        
        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $searchResult->getProductsSearchResult(),
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $currentPage,
                'sort_fields' => $sortFields,
            ],
            'filters' => $this->filtersDataProvider->getData($layerType),
            
        ];

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
