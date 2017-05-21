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
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;

/**
 * @api
 */
interface SourceRepositoryInterface
{
    /**
     * Save Source data.
     *
     * @param SourceInterface $source
     * @return void
     *
     * @throws CouldNotSaveException
     */
    public function save(SourceInterface $source);

    /**
     * Load Source data by given sourceId.
     *
     * @param int $sourceId
     * @return SourceInterface
     * @throws NoSuchEntityException
     */
    public function get($sourceId);

    /**
     * Load Source data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SourceSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);
}
