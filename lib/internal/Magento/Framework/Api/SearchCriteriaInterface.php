<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Search criteria interface.
 */
interface SearchCriteriaInterface
{
    const SORT_ASC = 1;
    const SORT_DESC = -1;

    /**
     * Get a list of filter groups.
     *
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     */
    public function getFilterGroups();

    /**
     * Get sort order.
     *
     * @return \Magento\Framework\Api\SortOrder[]|null
     */
    public function getSortOrders();

    /**
     * Get page size.
     *
     * @return int|null
     */
    public function getPageSize();

    /**
     * Get current page.
     *
     * @return int|null
     */
    public function getCurrentPage();
}
