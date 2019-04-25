<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api\Data;

/**
 * Specifies item and quantity for partial inventory transfer.
 *
 * @api
 */
interface PartialInventoryTransferItemInterface
{
    const SKU = 'sku';
    const QTY = 'qty';

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void;

    /**
     * @return float
     */
    public function getQty(): float;

    /**
     * @param float $qty
     */
    public function setQty(float $qty): void;
}
