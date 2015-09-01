<?php

namespace Magento\CatalogInventory\Api\Data;

use Magento\CatalogInventory\Api\ConfigurationInterface;

/**
 * Inventory record interface
 *
 * It is specially designed to be used on a frontend,
 * without possibility to change status or qty of record directly
 *
 * @api
 */
interface InventoryRecordInterface
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
     * Returns identifier of assigned inventory
     *
     * @return int
     */
    public function getInventoryId();

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

    /**
     * Return true if conditions of inventory record
     * allow purchase of the product
     *
     * @return bool
     */
    public function isSalable();

    /**
     * Returns instance of inventory interface
     *
     * Object should be set by inventory record repository
     *
     * @return InventoryInterface
     */
    public function getInventory();

    /**
     * Returns configuration object that is going to be used as configuration options
     *
     * Object should be set by inventory record repository
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration();
}
