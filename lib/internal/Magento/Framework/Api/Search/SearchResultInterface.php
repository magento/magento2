<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SearchResultInterface
 *
 * @api
 * @since 2.0.0
 */
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
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null);

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     * @since 2.0.0
     */
    public function getAggregations();

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     * @since 2.0.0
     */
    public function setAggregations($aggregations);

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     * @since 2.0.0
     */
    public function getSearchCriteria();
}
