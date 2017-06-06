<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;

/**
 * Inventory Error Interface
 *
 * @api
 */
interface InventoryErrorInterface
{
    const ERROR_LOW_STOCK = 1;
    const ERROR_NO_RECORD = 2;
    const ERROR_NO_DELIVERY = 3;

    /**
     * Returns a related inventory record to the error
     *
     * @return InventoryRecordInterface|null
     */
    public function getInventoryRecord();

    /**
     * Returns a related inventory criteria request to the error
     *
     * @return InventoryRecordCriteriaInterface|null
     */
    public function getInventoryCriteria();

    /**
     * Returns a message that happened
     *
     * @return string
     */
    public function getMessage();

    /**
     * Returns a message code for inventory error
     *
     * @return int
     */
    public function getCode();
}
