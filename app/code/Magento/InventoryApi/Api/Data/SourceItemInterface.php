<?php
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SourceItemInterface
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
     * Constants for status value
     */
    const STOCK_STATUS_IS_IN_STOCK = 1;
    const STOCK_STATUS_IS_OUT_OF_STOCK = 0;

    /**
     * Get source item id.
     *
     * @return int
     */
    public function getSourceItemId();

    /**
     * Get source item sku.
     *
     * @return string
     */
    public function getSku();

    /**
     * Set source item sku.
     *
     * @param string $sku
     * @return void
     */
    public function setSku($sku);

    /**
     * Get source id.
     *
     * @return int
     */
    public function getSourceId();

    /**
     * Set source id.
     *
     * @param int $sourceId
     * @return void
     */
    public function setSourceId($sourceId);

    /**
     * Get source item quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Set source item quantity.
     *
     * @param float $quantity
     * @return void
     */
    public function setQuantity($quantity);

    /**
     * Get source item status (One of self::STOCK_STATUS_*).
     *
     * @return bool
     */
    public function getStatus();

    /**
     * Set source item status (One of self::STOCK_STATUS_*).
     *
     * @param bool $status
     * @return void
     */
    public function setStatus($status);
}
