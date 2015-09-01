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
interface WarehouseConfigurationInterface extends ExtensibleDataInterface
{
    /**
     * Sets inventory identifier for warehouse configuration
     *
     * @param int $identifier
     * @return $this
     */
    public function setId($identifier);

    /**
     * Returns related inventory identifier for configuration
     *
     * @return int
     */
    public function getId();

    /**
     * Sets is managed flag
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsManaged($flag);

    /**
     * Returns is managed flag
     *
     * @return int|null
     */
    public function getIsManaged();

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
     * Sets flag for decimal values in quantity
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsDecimalQuantity($flag);

    /**
     * Returns flag for decimal values in quantity
     *
     * @return int|null
     */
    public function getIsDecimalQuantity();

    /**
     * Sets flag for allowed reservation below minimum available quantity
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsAllowedReservationBelowMinimumQty($flag);

    /**
     * Returns flag for allowed reservation below minimum available quantity
     *
     * @return int|null
     */
    public function getIsAllowedReservationBelowMinimumQty();

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
     * Sets quantity for notification about low quantity level
     *
     * @param float|null $quantity
     * @return $this
     */
    public function setLowStockNotificationQuantity($quantity);

    /**
     * Returns quantity for notification about low quantity level
     *
     * @return float|null
     */
    public function getLowStockNotificationQuantity();


    /**
     * Sets flag for enabled incremental quantity change
     *
     * @param int|null $flag
     * @return $this
     */
    public function setIsAllowedQuantityIncrement($flag);

    /**
     * Returns flag for enabled incremental quantity change
     *
     * @return int|null
     */
    public function getIsAllowedQuantityIncrement();

    /**
     * Sets quantity increment step
     *
     * @param float|null
     */
    public function setQuantityIncrement($increment);

    /**
     * Returns quantity increment step
     *
     * @return float|null
     */
    public function getQuantityIncrement();

    /**
     * Minimum available quantity for warehouse
     *
     * @param float|null $quantity
     * @return $this
     */
    public function setMinimumAvailableQuantity($quantity);

    /**
     * Minimum available quantity for warehouse
     *
     * @return float|null
     */
    public function getMinimumAvailableQuantity();

    /**
     * Sets minimum quantity for a reservation request in warehouse
     *
     * @param float|null $quantity
     * @return $this
     */
    public function setMinimumReservationQuantity($quantity);

    /**
     * Returns minimum quantity for a reservation request in warehouse
     *
     * @return float|null
     */
    public function getMinimumReservationQuantity();


    /**
     * Sets maximum quantity for a reservation request in warehouse
     *
     * @param float|null $quantity
     * @return $this
     */
    public function setMaximumReservationQuantity($quantity);

    /**
     * Returns maximum quantity for a reservation request in this inventory
     *
     * @return float|null
     */
    public function getMaximumReservationQuantity();

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
