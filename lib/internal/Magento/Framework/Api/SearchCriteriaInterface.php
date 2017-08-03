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
 * @since 2.0.0
 */
interface SearchCriteriaInterface
{
    /**
     * Get a list of filter groups.
     *
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     * @since 2.0.0
     */
    public function getFilterGroups();

    /**
     * Set a list of filter groups.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     * @since 2.0.0
     */
    public function setFilterGroups(array $filterGroups = null);

    /**
     * Get sort order.
     *
     * @return \Magento\Framework\Api\SortOrder[]|null
     * @since 2.0.0
     */
    public function getSortOrders();

    /**
     * Set sort order.
     *
     * @param \Magento\Framework\Api\SortOrder[] $sortOrders
     * @return $this
     * @since 2.0.0
     */
    public function setSortOrders(array $sortOrders = null);

    /**
     * Get page size.
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getPageSize();

    /**
     * Set page size.
     *
     * @param int $pageSize
     * @return $this
     * @since 2.0.0
     */
    public function setPageSize($pageSize);

    /**
     * Get current page.
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCurrentPage();

    /**
     * Set current page.
     *
     * @param int $currentPage
     * @return $this
     * @since 2.0.0
     */
    public function setCurrentPage($currentPage);
}
