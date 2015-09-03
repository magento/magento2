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
interface WarehouseRecordInterface extends InventoryRecordDataInterface, ExtensibleDataInterface
{
    /**
     * Returns identifier of assigned warehouse
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Sets warehouse identifier
     *
     * @param int $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId);

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
