<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * @api
 */
interface SourceRepositoryInterface
{
    /**
     * Save Source data.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceInterface $source
     * @return \Magento\InventoryApi\Api\Data\SourceInterface
     *
     * @throws CouldNotSaveException
     */
    public function save(SourceInterface $source);

    /**
     * Load Source data by given sourceId.
     *
     * @param int $sourceId
     * @return \Magento\InventoryApi\Api\Data\SourceInterface
     * @throws NoSuchEntityException
     */
    public function get($sourceId);

    /**
     * Load Source data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\SourceSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);
}
