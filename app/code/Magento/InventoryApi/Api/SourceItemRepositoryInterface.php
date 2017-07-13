<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * This is Facade for basic operations with Source Item
 * The method save is absent, due to different semantic (save multiple)
 *
 * @see SourceItemSaveInterface
 * @api
 */
interface SourceItemRepositoryInterface
{
    /**
     * Get Source Item data by given sourceItemId. If you want to create plugin on get method, also you need to create
     * separate plugin on getList method, because entity loading way is different for these methods
     *
     * @param int $sourceItemId
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sourceItemId);

    /**
     * Load Source Item data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Source Item data by given sourceItemId
     *
     * @param int $sourceItemId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete($sourceItemId);
}
