<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * SearchResults Service Data Object used for the search service requests
 */
class SearchResults extends AbstractSimpleObject implements SearchResultsInterface
{
    const KEY_ITEMS = 'items';
    const KEY_SEARCH_CRITERIA = 'search_criteria';
    const KEY_TOTAL_COUNT = 'total_count';

    /**
     * Get items
     *
     * @return \Magento\Framework\Api\AbstractExtensibleObject[]
     */
    public function getItems()
    {
        return $this->_get(self::KEY_ITEMS) === null ? [] : $this->_get(self::KEY_ITEMS);
    }

    /**
     * Set items
     *
     * @param \Magento\Framework\Api\AbstractExtensibleObject[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * Get search criteria
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    public function getSearchCriteria()
    {
        return $this->_get(self::KEY_SEARCH_CRITERIA);
    }

    /**
     * Set search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->setData(self::KEY_SEARCH_CRITERIA, $searchCriteria);
    }

    /**
     * Get total count
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_get(self::KEY_TOTAL_COUNT);
    }

    /**
     * Set total count
     *
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count)
    {
        return $this->setData(self::KEY_TOTAL_COUNT, $count);
    }
}
