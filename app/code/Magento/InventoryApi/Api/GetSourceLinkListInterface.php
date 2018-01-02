<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterface;

/**
 * Find StockSourceLink list by SearchCriteria API
 *
 * @api
 */
interface GetSourceLinkListInterface
{
    /**
     * Find StockSourceLink list by given SearchCriteria
     * SearchCriteria is not required because load all stocks is useful case
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return StockSourceLinkSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): StockSourceLinkSearchResultsInterface;
}
