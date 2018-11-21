<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

/**
 * Represents relation between Stock and Source entities.
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface StockSourceLinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCK_ID = 'stock_id';
    const SOURCE_CODE = 'source_code';
    const PRIORITY = 'priority';
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
     * Get source code of the link
     *
     * @return string|null
     */
    public function getSourceCode(): ?string;

    /**
     * Set source code of the link
     *
     * @param string|null $sourceCode
     *
     * @return void
     */
    public function setSourceCode(?string $sourceCode): void;

    /**
     * Get priority of the link
     *
     * @return int|null
     */
    public function getPriority(): ?int;

    /**
     * Set priority of the link
     *
     * @param int $priority
     *
     * @return void
     */
    public function setPriority(?int $priority): void;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\StockSourceLinkExtensionInterface|null
     */
    public function getExtensionAttributes(): ?\Magento\InventoryApi\Api\Data\StockSourceLinkExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\StockSourceLinkExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\StockSourceLinkExtensionInterface $extensionAttributes
    ): void;
}
