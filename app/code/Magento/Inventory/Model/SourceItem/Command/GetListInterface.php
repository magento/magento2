<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;

/**
 * Find SourceItems by SearchCriteria command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial GetList call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * We need to have this command for direct work with Source Items because this object contains
 * additional data like as qty, status (for example can de searchable by additional field)
 *
 * @see \Magento\InventoryApi\Api\SourceItemRepositoryInterface
 * @api
 */
interface GetListInterface
{
    /**
     * Find SourceItems by given SearchCriteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SourceItemSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): SourceItemSearchResultsInterface;
}
