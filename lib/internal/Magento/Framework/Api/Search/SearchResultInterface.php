<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\SearchResultsInterface;

interface SearchResultInterface extends SearchResultsInterface
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
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations();

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations);

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     */
    public function getSearchCriteria();
}
