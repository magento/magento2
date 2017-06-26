<?php
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SourceItemInterface extends ExtensibleDataInterface
{
    const SKU = 'sku';
    const SOURCE_ID = 'source_id';
    const SOURCE_ITEM_ID = 'source_item_id';
    const QUANTITY = 'quantity';
    const STATUS = 'status';
    const IS_IN_STOCK = TRUE;
    const IS_OUT_OF_STOCK = FALSE;

    /**
     * @return int
     */
    public function getSku();

    /**
     * @param $sku
     * @return int
     */
    public function setSku($sku);

    /**
     * @return int
     */
    public function getSourceId();

    /**
     * @return int
     */
    public function getSourceItemId();

    /**
     * @return float
     */
    public function getQuantity();

    /**
     * @param $quantity
     * @return float
     */
    public function setQuantity($quantity);

    /**
     * @return bool
     */
    public function getStatus();

    /**
     * @param $status
     * @return bool
     */
    public function setStatus($status);
}