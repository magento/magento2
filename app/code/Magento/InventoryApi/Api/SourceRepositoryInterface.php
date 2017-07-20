<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * This is Facade for basic operations with Source
 * There is no delete method. It is related to that Source can't be deleted due to we don't want miss data
 * related to Sources (like as order information). But Source can be disabled
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceRepositoryInterface
{
    /**
     * Save Source data
     *
     * @param \Magento\InventoryApi\Api\Data\SourceInterface $source
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(SourceInterface $source);

    /**
     * Get Source data by given sourceId. If you want to create plugin on get method, also you need to create separate
     * plugin on getList method, because entity loading way is different for these methods
     *
     * @param int $sourceId
     * @return \Magento\InventoryApi\Api\Data\SourceInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
