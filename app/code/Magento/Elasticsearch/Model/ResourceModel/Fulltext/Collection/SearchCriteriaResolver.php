<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;

/**
 * Resolve specific attributes for search criteria.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class SearchCriteriaResolver implements SearchCriteriaResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $builder;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * @var int
     */
    private $size;

    /**
     * @var array|null
     */
    private $orders;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @param SearchCriteriaBuilder $builder
     * @param string $searchRequestName
     * @param int $currentPage
     * @param int $size
     * @param array|null $orders
     */
    public function __construct(
        SearchCriteriaBuilder $builder,
        string $searchRequestName,
        int $currentPage,
        int $size,
        ?array $orders = null
    ) {
        $this->builder = $builder;
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
        $searchCriteria = $this->builder->create();
        $searchCriteria->setRequestName($this->searchRequestName);
        $searchCriteria->setSortOrders($this->orders);
        $searchCriteria->setCurrentPage($this->currentPage - 1);
        if ($this->size) {
            $searchCriteria->setPageSize($this->size);
        }

        return $searchCriteria;
    }
}
