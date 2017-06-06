<?php

namespace Magento\CatalogInventory\Api;

/**
 * Base configuration interface for inventory
 *
 * @api
 */
interface ConfigurationInterface
{
    /**
     * Flag for enabled inventory management
     *
     * @return bool
     */
    public function isManaged();

    /**
     * Flag for allowed management of qty changes
     * for a specified product type
     *
     * @param $productTypeId
     * @return bool
     */
    public function isQuantityManagedForProductType($productTypeId);

    /**
     * If it is allowed to change quantity,
     * when reservation is processed
     *
     * @return bool
     */
    public function isAllowedQuantityChange();

    /**
     * Checks if qty is a decimal value
     *
     * @return bool
     */
    public function isDecimalQuantity();

    /**
     * Returns flag for allowed reservation
     * below minimum available quantity
     *
     * @return bool
     */
    public function isAllowedReservationBelowMinimumQty();

    /**
     * Returns true if it is allowed to return qty for cancelled reservation
     *
     * @return bool
     */
    public function isAllowedReservationCancel();

    /**
     * Returns true if it is allowed to return qty for reversed reservation
     *
     * @return bool
     */
    public function isAllowedReservationReturn();

    /**
     * Returns true if it is allowed to view out of stock products
     *
     * @return bool
     */
    public function isOutOfStockVisible();

    /**
     * Returns quantity for notification about low quantity level
     *
     * @return float
     */
    public function getLowStockNotificationQuantity();

    /**
     * Returns true if incremental quantity change is enabled
     *
     * @return bool
     */
    public function isAllowedQuantityIncrement();

    /**
     * Returns quantity increment step
     *
     * @return float
     */
    public function getQuantityIncrement();

    /**
     * Minimum available quantity for inventory object
     *
     * @return float
     */
    public function getMinimumAvailableQuantity();

    /**
     * Returns minimum quantity for a reservation request in this inventory
     *
     * @return float
     */
    public function getMinimumReservationQuantity();

    /**
     * Returns maximum quantity for a reservation request in this inventory
     *
     * @return float
     */
    public function getMaximumReservationQuantity();

    /**
     * Returns list of availability status codes
     *
     * @return string[]
     */
    public function getAvailabilityStatusCodes();
}
