<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;

/**
 * Sugar service with finder methods for SourceItems
 */
interface SourceItemFinderInterface
{
    /**
     * Find SourceItems by SKU
     *
     * @param string $sku
     * @return \Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface
     */
    public function findBySku(string $sku): SourceItemSearchResultsInterface;
}
