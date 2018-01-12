<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

/**
 * Sugar service for get stock if.
 */
interface GetStockIdForCurrentWebsiteInterface
{
    /**
     * Get stock id by code for current website.
     *
     * @return int
     */
    public function execute(): int;
}
