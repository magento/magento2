<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

/**
 * Result of how we will deduct product qty from different Sources
 *
 * @api
 */
interface SourceSelectionResultInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface[]
     */
    public function getSourceSelectionItems(): array;

    /**
     * @return bool
     */
    public function isShippable(): bool;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?SourceSelectionResultExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultExtensionInterface $extensionAttributes
    ): void;
}
