<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Data Object for SearchCriteria
 * @codeCoverageIgnore
 */
class SearchCriteria extends AbstractSimpleObject implements SearchCriteriaInterface
{
    /**#@+
     * Constants for Data Object keys
     */
    const FILTER_GROUPS = 'filter_groups';
    const SORT_ORDERS = 'sort_orders';
    const PAGE_SIZE = 'page_size';
    const CURRENT_PAGE = 'current_page';

    /**
     * Get a list of filter groups.
     *
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     */
    public function getFilterGroups()
    {
        $filterGroups = $this->_get(self::FILTER_GROUPS);
        return is_array($filterGroups) ? $filterGroups : [];
    }

    /**
     * Get sort order.
     *
     * @return \Magento\Framework\Api\SortOrder[]|null
     */
    public function getSortOrders()
    {
        return $this->_get(self::SORT_ORDERS);
    }

    /**
     * Get page size.
     *
     * @return int|null
     */
    public function getPageSize()
    {
        return $this->_get(self::PAGE_SIZE);
    }

    /**
     * Get current page.
     *
     * @return int|null
     */
    public function getCurrentPage()
    {
        return $this->_get(self::CURRENT_PAGE);
    }

    /**
     * Set a list of filter groups.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     */
    public function setFilterGroups(array $filterGroups = null)
    {
        return $this->setData(self::FILTER_GROUPS, $filterGroups);
    }

    /**
     * Set sort order.
     *
     * @param \Magento\Framework\Api\SortOrder[] $sortOrders
     * @return $this
     */
    public function setSortOrders(array $sortOrders = null)
    {
        return $this->setData(self::SORT_ORDERS, $sortOrders);
    }

    /**
     * Set page size.
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        return $this->setData(self::PAGE_SIZE, $pageSize);
    }

    /**
     * Set current page.
     *
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage)
    {
        return $this->setData(self::CURRENT_PAGE, $currentPage);
    }
}
