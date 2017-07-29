<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StockItemInterface
 * @api
 */
interface StockItemInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCK_ID = 'stock_id';
    const SKU = 'sku';
    const QUANTITY = 'quantity';
    const STATUS = 'status';
    /**#@-*/

    /**#@+
     * Source items status values
     */
    const STATUS_OUT_OF_STOCK = 0;
    const STATUS_IN_STOCK = 1;
    /**#@-*/
    
    /**
     * Get stock id
     *
     * @return int
     */
    public function getStockId();

    /**
     * Set stock id
     *
     * @param int $stockId
     * @return void
     */
    public function setStockId($stockId);
    
    /**
     * Get stock item sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set stock item sku
     *
     * @param string $sku
     * @return void
     */
    public function setSku($sku);

    /**
     * Get stock item quantity
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Set stock item quantity
     *
     * @param float $quantity
     * @return void
     */
    public function setQuantity($quantity);

    /**
     * Get stock item status (One of self::STATUS_*)
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set stock item status (One of self::STATUS_*)
     *
     * @param int $status
     * @return int
     */
    public function setStatus($status);

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\StockExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(StockExtensionInterface $extensionAttributes);
}
