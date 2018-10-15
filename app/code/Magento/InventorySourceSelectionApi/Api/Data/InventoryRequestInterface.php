<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Request products in a given Qty and StockId
 *
 * @api
 */
interface InventoryRequestInterface extends ExtensibleDataInterface
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
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(InventoryRequestExtensionInterface $extensionAttributes);
}
