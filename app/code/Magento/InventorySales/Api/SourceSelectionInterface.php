<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Api;

/**
 * @api
 */
interface SourceSelectionInterface
{
    /**
     * Get source code
     *
     * @return string
     */
    public function getSourceCode(): string;

    /**
     * Set source code
     *
     * @param string $sourceCode
     */
    public function setSourceCode(string $sourceCode);

    /**
     * Get quantity for this source
     *
     * @return float
     */
    public function getQty(): float;

    /**
     * Set quantity for this source
     *
     * @param float $qty
     */
    public function setQty(float $qty);
}
