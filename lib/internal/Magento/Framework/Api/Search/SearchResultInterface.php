<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

interface SearchResultInterface extends  SearchResultsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const TOTAL_COUNT = 'total_count';
    const SEARCH_CRITERIA = 'search_criteria';
    const ITEMS = 'items';
    const AGGREGATIONS = 'aggregations';
    /**#@-*/

    /**
     * @return DocumentInterface[]
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param DocumentInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);

    /**
     * @return AggregationInterface
     */
    public function getAggregations();

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations);

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria();
}
