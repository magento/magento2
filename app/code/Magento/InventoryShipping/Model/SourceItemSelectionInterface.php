<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

/**
 * Represents part of the shipping algorithm result
 *
 * @api
 */
interface SourceItemSelectionInterface
{
    /**
     * Get item SKU
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Get quantity which will be deducted for this source
     *
     * @return float
     */
    public function getQty(): float;

    /**
     * Get available quantity for this source
     *
     * @return float
     */
    public function getQtyAvailable(): float;
}
