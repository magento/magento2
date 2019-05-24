<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;

/**
 * Resolve specific attributes for search criteria.
 */
class SearchCriteriaResolver implements SearchCriteriaResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $builder;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * @var int
     */
    private $size;

    /**
     * @var array
     */
    private $orders;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * SearchCriteriaResolver constructor.
     * @param SearchCriteriaBuilder $builder
     * @param Collection $collection
     * @param string $searchRequestName
     * @param int $currentPage
     * @param int $size
     * @param array $orders
     */
    public function __construct(
        SearchCriteriaBuilder $builder,
        Collection $collection,
        string $searchRequestName,
        int $currentPage,
        int $size,
        ?array $orders
    ) {
        $this->builder = $builder;
        $this->collection = $collection;
        $this->searchRequestName = $searchRequestName;
        $this->currentPage = $currentPage;
        $this->size = $size;
        $this->orders = $orders;
    }

    /**
     * @inheritdoc
     */
    public function resolve(): SearchCriteria
    {
        $this->builder->setPageSize($this->size);
        $searchCriteria = $this->builder->create();
        $searchCriteria->setRequestName($this->searchRequestName);
        $searchCriteria->setSortOrders(array_merge(['relevance' => 'DESC'], $this->orders));
        $searchCriteria->setCurrentPage($this->currentPage - 1);

        return $searchCriteria;
    }
}
