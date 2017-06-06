<?php

namespace Magento\CatalogInventory\Api\Data;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Editable representation of inventory configuration interface
 *
 * In case if value is null, it means that parent value is going to be used
 *
 * @api
 */
interface WarehouseConfigurationInterface extends WarehouseRecordConfigurationDataInterface, ExtensibleDataInterface
{
    /**
     * Sets inventory identifier for warehouse configuration
     *
     * @param int $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId);

    /**
     * Returns related inventory identifier for configuration
     *
     * @return int
     */
    public function getWarehouseId();

    /**
     * Sets allowed product type ids,
     * that are going to be used by warehouse for qty changes
     *
     * @param int[]|null $productTypeIds
     * @return $this
     */
    public function setQuantityManagedProductTypeIds($productTypeIds);

    /**
     * Sets allowed product type ids,
     * that are going to be used by warehouse for qty changes
     *
     * @return int[]|null
     */
    public function getQuantityManagedProductTypeIds();

    /**
     * Sets flag for allowed quantity change
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsAllowedQuantityChange($flag);

    /**
     * Returns flag for allowed quantity change
     *
     * @return int|null
     */
    public function getIsAllowedQuantityChange();

    /**
     * Sets flag for allowing return quantities for cancelled reservation
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsAllowedReservationCancel($flag);

    /**
     * Returns flag for allowing return quantities for cancelled reservation
     *
     * @return $this
     */
    public function getIsAllowedReservationCancel();

    /**
     * Returns flag for allowing return quantities for reversed reservation
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsAllowedReservationReturn($flag);

    /**
     * Returns flag for allowing return quantities for reversed reservation
     *
     * @return int|null
     */
    public function getIsAllowedReservationReturn();

    /**
     * Sets flag for allowed view of out of stock products
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsOutOfStockVisible($flag);

    /**
     * Returns flag for allowed view of out of stock products
     *
     * @return int|null
     */
    public function getIsOutOfStockVisible();

    /**
     * Sets availability status codes for warehouse configuration
     *
     * @param string[]|null $codes
     * @return $this
     */
    public function setAvailabilityStatusCodes($codes);

    /**
     * Returns availability status codes for warehouse configuration
     *
     * @return string[]|null
     */
    public function getAvailabilityStatusCodes();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogInventory\Api\Data\WarehouseConfigurationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogInventory\Api\Data\WarehouseConfigurationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\WarehouseConfigurationExtensionInterface $extensionAttributes
    );
}
