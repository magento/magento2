<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Search criteria interface.
 *
 * @api
 */
interface SearchCriteriaInterface
{
    /**
     * Get a list of filter groups.
     *
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     */
    public function getFilterGroups();

    /**
     * Set a list of filter groups.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     */
    public function setFilterGroups(array $filterGroups = null);

    /**
     * Get sort order.
     *
     * @return \Magento\Framework\Api\SortOrder[]|null
     */
    public function getSortOrders();

    /**
     * Set sort order.
     *
     * @param \Magento\Framework\Api\SortOrder[] $sortOrders
     * @return $this
     */
    public function setSortOrders(array $sortOrders = null);

    /**
     * Get page size.
     *
     * @return int|null
     */
    public function getPageSize();

    /**
     * Set page size.
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize);

    /**
     * Get current page.
     *
     * @return int|null
     */
    public function getCurrentPage();

    /**
     * Set current page.
     *
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage);
}
