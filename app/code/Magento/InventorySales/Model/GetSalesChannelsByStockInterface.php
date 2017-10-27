<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

/**
 * Get linked the linked sales channels for given stockId.
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */

interface GetSalesChannelsByStockInterface
{
    /**
     * Get linked sales channels objects for given stockId.
     *
     * @param int $stockId
     * @return SalesChannel[]
     */
    public function get(int $stockId): array;
}
