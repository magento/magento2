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
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface GetStockSourceLinksInterface
{
    /**
     * Find StockSourceLink list by given SearchCriteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): StockSourceLinkSearchResultsInterface;
}
