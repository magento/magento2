<?php

namespace Magento\CatalogInventory\Api\Data;

/**
 * Interface of shared inventory record data with warehouse record
 *
 */
interface InventoryRecordDataInterface
{
    /**
     * Returns identifier of inventory record
     *
     * @return int
     */
    public function getId();

    /**
     * Returns identifier of assigned product
     *
     * @return int
     */
    public function getProductId();

    /**
     * Returns available quantity to create a reservation
     *
     * In case of allowed values below minimal inventory amount, this value can be negative.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Returns availability status code of the inventory record
     *
     * @return string
     */
    public function getAvailabilityStatusCode();
}
