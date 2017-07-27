<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * This is Facade for basic operations with SourceItem
 *
 * The method save is absent, due to different semantic (save multiple)
 * @see SourceItemSaveInterface
 *
 * There is no get method because SourceItem identifies by compound identifier (sku and source_id),
 * so need to use getList() method
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemRepositoryInterface
{
    /**
     * Load Source Item data collection by given search criteria
     *
     * We need to have this method for direct work with Source Items because this object contains
     * additional data like as qty, status (for example can de searchable by additional field)
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Source Item data
     *
     * @param SourceItemInterface $sourceItem
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(SourceItemInterface $sourceItem);
}
