<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents amount of product on physical storage
 *
 * @api
 */
interface SourceItemInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SKU = 'sku';
    const SOURCE_ID = 'source_id';
    const SOURCE_ITEM_ID = 'source_item_id';
    const QUANTITY = 'quantity';
    const STATUS = 'status';
    /**#@-*/

    /**
     * Get source item id
     *
     * @return int
     */
    public function getSourceItemId();

    /**
     * Get source item sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set source item sku
     *
     * @param string $sku
     * @return void
     */
    public function setSku($sku);

    /**
     * Get source id
     *
     * @return int
     */
    public function getSourceId();

    /**
     * Set source id
     *
     * @param int $sourceId
     * @return void
     */
    public function setSourceId($sourceId);

    /**
     * Get source item quantity
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Set source item quantity
     *
     * @param float $quantity
     * @return void
     */
    public function setQuantity($quantity);

    /**
     * Get source item status (One of \Magento\Inventory\Model\OptionSource\SourceItemStatus::SOURCE_ITEM_STATUS_*)
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set source item status (One of \Magento\Inventory\Model\OptionSource\SourceItemStatus::SOURCE_ITEM_STATUS_*)
     *
     * @param int $status
     * @return int
     */
    public function setStatus($status);

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\SourceItemExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\SourceItemExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceItemExtensionInterface $extensionAttributes
    );
}
