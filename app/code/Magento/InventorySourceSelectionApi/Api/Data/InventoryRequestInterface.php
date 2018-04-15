<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

/**
 * Request products in a given Qty and StockId
 *
 * @api
 */
interface InventoryRequestInterface
{
    /**
     * @return int
     */
    public function getStockId(): int;

    /**
     * @return \Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface[]
     */
    public function getItems(): array;
}
