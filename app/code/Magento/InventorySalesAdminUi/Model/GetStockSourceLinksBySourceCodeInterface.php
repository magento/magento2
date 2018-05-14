<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Model;

use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterface;

/**
 * Sugar service for find StockSourceLinks by source code
 *
 * @api
 */
interface GetStockSourceLinksBySourceCodeInterface
{
    /**
     * @param string $sourceCode
     * @return StockSourceLinkSearchResultsInterface
     */
    public function execute(string $sourceCode): StockSourceLinkSearchResultsInterface;
}
