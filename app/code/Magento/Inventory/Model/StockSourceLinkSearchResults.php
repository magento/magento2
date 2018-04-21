<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchResults;
use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterface;

class StockSourceLinkSearchResults extends SearchResults implements StockSourceLinkSearchResultsInterface
{
}
