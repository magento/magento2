<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Search results interface.
 *
 * @api
 * @since 2.0.0
 */
interface SearchResultsInterface
{
    /**
     * Get items list.
     *
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     * @since 2.0.0
     */
    public function getSearchCriteria();

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @since 2.0.0
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get total count.
     *
     * @return int
     * @since 2.0.0
     */
    public function getTotalCount();

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @since 2.0.0
     */
    public function setTotalCount($totalCount);
}
