<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;

/**
 * Sugar service for find SourceItems by SKU
 *
 * @api
 */
interface GetSourceItemsBySkuInterface
{
    /**
     * @param string $sku
     * @return SourceItemSearchResultsInterface
     */
    public function execute(string $sku): SourceItemSearchResultsInterface;
}
