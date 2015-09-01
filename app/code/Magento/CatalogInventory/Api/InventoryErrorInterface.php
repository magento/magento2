<?php

namespace Magento\CatalogInventory\Api;
use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;

/**
 * Inventory Error Interface
 *
 */
interface InventoryErrorInterface
{
    /**
     * Returns a related inventory record to the error
     *
     * @return InventoryRecordInterface|null
     */
    public function getInventoryRecord();

    /**
     * Returns a related inventory request to the error
     *
     * @return InventoryRequestInterface|null
     */
    public function getInventoryRequest();

    /**
     * Returns a message that happened
     *
     * @return string
     */
    public function getMessage();


}

