<?php

namespace Magento\CatalogInventory\Api\Data;

/**
 * Interface for contract that is shared between warehouse record configuration and warehouse configuration
 *
 *
 */
interface WarehouseRecordConfigurationDataInterface
{
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
     * Returns maximum quantity for a reservation request in this warehouse
     *
     * @return float|null
     */
    public function getMaximumReservationQuantity();
}
