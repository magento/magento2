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
interface InventoryRequestInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get Stock Id
     *
     * @return int
     */
    public function getStockId(): int;

    /**
     * Get Items
     *
     * @return \Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface[]
     */
    public function getItems(): array;

    /**
     * Set Stock Id
     *
     * @param int $stockId
     * @return void
     */
    public function setStockId(int $stockId): void;

    /**
     * Set Items
     *
     * @param \Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?InventoryRequestExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterface $extensionAttributes
    ): void;
}
