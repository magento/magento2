<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\DataProvider\Base;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Search\Api\SearchInterface;

/**
 * Get the search suggestion result count
 */
class GetSuggestionFrequency implements GetSuggestionFrequencyInterface
{
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * Search suggestion frequency constructor.
     *
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchInterface $search
     */
    public function __construct(
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchInterface $search
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->search = $search;
    }
    /**
     * Get the search suggestion frequency
     *
     * @param string $text
     * @return int
     */
    public function execute(string $text): int
    {
        $this->filterBuilder->setField('search_term');
        $this->filterBuilder->setValue($text);
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setRequestName('quick_search_container');
        $searchCriteria->setCurrentPage(1);
        $searchCriteria->setPageSize(1);
        $searchCriteria->setSortOrders([]);
        $searchResult = $this->search->search($searchCriteria);
        return $searchResult->getTotalCount();
    }
}
