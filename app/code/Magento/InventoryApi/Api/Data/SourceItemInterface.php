<?php
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

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
     * Constants for status value.
     */
    const IS_IN_STOCK = TRUE;
    const IS_OUT_OF_STOCK = FALSE;

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
     * Get source item source id.
     *
     * @return int
     */
    public function getSourceId();

    /**
     * Get source item source id.
     *
     * @return int
     */
    public function getSourceItemId();

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
     * Get source item status.
     *
     * @return bool
     */
    public function getStatus();

    /**
     * Set source item status.
     *
     * @param bool $status
     * @return void
     */
    public function setStatus($status);
}
