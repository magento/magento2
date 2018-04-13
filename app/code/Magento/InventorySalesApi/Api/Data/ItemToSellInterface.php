<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api\Data;

/**
 * DTO used as the type for values of `$items` array passed to RegisterSalesEventInterface::execute()
 * @see \Magento\InventorySalesApi\Api\RegisterSalesEventInterface
 *
 * @api
 */
interface ItemToSellInterface
{
    public function getSku(): string;

    public function getQuantity(): float;

    public function setSku(string $sku);

    public function setQuantity(float $qty);
}
