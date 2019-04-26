<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api\Data;

/**
 * Class ExportStockIndexDataResultInterface for result Inventory stock index dump export
 *
 * @api
 */
interface ProductStockIndexDataInterface
{
    public const QTY = 'qty';
    public const IS_SALABLE = 'is_salable';
    public const SKU = 'sku';

    /**
     * Provides product SKU
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Provides product QTY
     *
     * @return float
     */
    public function getQty(): float;

    /**
     * Provides product is salable flag
     *
     * @return bool
     */
    public function getIsSalable(): bool;

    /**
     * Sets SKU
     *
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void;

    /**
     * Sets QTY
     *
     * @param float $qty
     * @return void
     */
    public function setQty(float $qty): void;

    /**
     * Sets is salable flag
     *
     * @param bool $isSalable
     * @return void
     */
    public function setIsSalable(bool $isSalable): void;

}
