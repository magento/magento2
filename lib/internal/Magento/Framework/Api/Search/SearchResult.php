<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\SearchCriteriaInterface as BaseSearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

class SearchResult extends AbstractSimpleObject implements SearchResultInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->_get(self::AGGREGATIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        return $this->setData(self::AGGREGATIONS, $aggregations);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->_get(self::ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria()
    {
        return $this->_get(self::SEARCH_CRITERIA);
    }

    /**
     * Set search criteria.
     *
     * @param BaseSearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(BaseSearchCriteriaInterface $searchCriteria = null)
    {
        return $this->setData(self::SEARCH_CRITERIA, $searchCriteria);
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_get(self::TOTAL_COUNT);
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this->setData(self::TOTAL_COUNT, $totalCount);
    }
}
