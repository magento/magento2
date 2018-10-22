<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

/**
 * Represents product aggregation among some different physical storages (in technical words, it is an index)
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface StockInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCK_ID = 'stock_id';
    const NAME = 'name';
    /**#@-*/

    /**
     * Get stock id
     *
     * @return int|null
     */
    public function getStockId(): ?int;

    /**
     * Set stock id
     *
     * @param int|null $stockId
     * @return void
     */
    public function setStockId(?int $stockId): void;

    /**
     * Get stock name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set stock name
     *
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\StockExtensionInterface|null
     */
    public function getExtensionAttributes(): ?\Magento\InventoryApi\Api\Data\StockExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
    ): void;
}
