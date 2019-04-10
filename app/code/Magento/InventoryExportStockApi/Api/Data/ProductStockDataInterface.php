<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api\Data;

/**
 * Interface ProductStockDataInterface
 */
interface ProductStockDataInterface
{
    public const SKU = 'sku';
    public const QTY = 'qty';

    /**
     * Sets SKU
     *
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void;

    /**
     * Provides SKU
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Sets QTY
     *
     * @param int $qty
     * @return void
     */
    public function setQty(int $qty): void;

    /**
     * Provides QTY
     *
     * @return int
     */
    public function getQty(): int;
}
