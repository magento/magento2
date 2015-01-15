<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * SearchResults Service Data Object used for the search service requests
 */
class SearchResults extends \Magento\Framework\Api\AbstractExtensibleObject
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
        return is_null($this->_get(self::KEY_ITEMS)) ? [] : $this->_get(self::KEY_ITEMS);
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
     * Get total count
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_get(self::KEY_TOTAL_COUNT);
    }
}
