<?php

namespace Magento\CatalogInventory\Api\Data;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Warehouse record interface
 *
 * It is used only to manage stock status
 * qty of non virtual warehouse inventory
 *
 * @api
 */
interface WarehouseRecordInterface extends ExtensibleDataInterface
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
     * Returns available quantity of product in the specified warehouse
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Sets available quantity of product in the specified warehouse
     *
     * @param float $quantity
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * Sets a stock status for an item
     *
     * @param int $flag
     * @return $this
     */
    public function setIsSalable($flag);

    /**
     * Sets availability status code for warehouse record
     *
     * @param string $code
     * @return $this
     */
    public function setAvailabilityStatusCode($code);

    /**
     * Returns availability status code for warehouse record
     *
     * @return string
     */
    public function getAvailabilityStatusCode();

    /**
     * Returns instance of inventory interface
     *
     * Object should be set by inventory record repository
     *
     * @return InventoryInterface
     */
    public function getInventory();

    /**
     * Sets configuration object that is going to be used as configuration options
     *
     * @param WarehouseRecordConfigurationInterface $configuration
     * @return $this
     */
    public function setConfiguration(WarehouseRecordConfigurationInterface $configuration);

    /**
     * Returns configuration object that is going to be used for editing configuration options
     *
     * @return WarehouseRecordConfigurationInterface
     */
    public function getConfiguration();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogInventory\Api\Data\WarehouseRecordExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogInventory\Api\Data\WarehouseRecordExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\WarehouseRecordExtensionInterface $extensionAttributes
    );
}
