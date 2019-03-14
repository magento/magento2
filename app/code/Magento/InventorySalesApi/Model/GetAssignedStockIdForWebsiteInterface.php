<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model;

/**
 * Get assigned Stock id for Website (Service Provider Interface - SPI)
 *
 * @api
 */
interface GetAssignedStockIdForWebsiteInterface
{
    /**
     * Get assigned stock to website
     *
     * @param string $websiteCode
     * @return int|null
     */
    public function execute(string $websiteCode): ?int;
}
