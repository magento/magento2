<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

/**
 * Extension point for resolve stock id  (Service Provider Interface - SPI).
 * Provide own implementation of this interface if you have mismatch between internal and external stock id.
 *
 * @api
 */
interface StockIdResolverInterface
{
    /**
     * Resolve given stockid
     *
     * @param int $stockId
     * @return int
     */
    public function execute(int $stockId) : int;
}
