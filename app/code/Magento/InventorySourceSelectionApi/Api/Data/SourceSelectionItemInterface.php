<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

/**
 * Represents source selection result for the specific source and SKU
 *
 * @api
 */
interface SourceSelectionItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get source code
     *
     * @return string
     */
    public function getSourceCode(): string;

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
    public function getQtyToDeduct(): float;

    /**
     * Get available quantity for this source
     *
     * @return float
     */
    public function getQtyAvailable(): float;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemExtensionInterface|null
     */
    public function getExtensionAttributes(): ?SourceSelectionItemExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemExtensionInterface $extensionAttributes
    ): void;
}
