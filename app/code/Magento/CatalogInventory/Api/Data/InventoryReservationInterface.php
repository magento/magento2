<?php

namespace Magento\CatalogInventory\Api\Data;

/**
 * Inventory reservation interface
 *
 * Main idea
 *
 * @api
 */
interface InventoryReservationInterface
{
    /**
     * New reservation status
     *
     * Set before order is created
     *
     * @var string
     */
    const STATUS_NEW = 'new';

    /**
     * Confirmed reservation status
     *
     * Set at the moment when qty is deducted
     *
     * @var string
     */
    const STATUS_CONFIRMED = 'confirmed';

    /**
     * Error reservation status
     *
     * Set at the moment when qty is deducted,
     * but value is lower then allowed minimum qty for an item
     *
     * @var string
     */
    const STATUS_ERROR = 'error';

    /**
     * Complete reservation status
     *
     * Set when order is completed, e.g. stock is not cancelable
     *
     * @var string
     */
    const STATUS_COMPLETE = 'complete';

    /**
     * Canceled reservation status
     *
     * Set when order stock is canceled and qty is reversed back
     *
     * @var string
     */
    const STATUS_CANCELED = 'canceled';

    /**
     * Returned reservation status
     *
     * Set when order stock is returned and qty is reversed back
     *
     * @var string
     */
    const STATUS_RETURNED = 'returned';


    /**
     * Returns reservation identifier
     *
     * @return string
     */
    public function getId();

    /**
     * Returns inventory reservation status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Returns reserved quantity
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Returns inventory identifier
     *
     * @return int
     */
    public function getInventoryId();

    /**
     * Returns product identifier
     *
     * @return int
     */
    public function getProductId();

    /**
     * Is it allowed to cancel reservation
     *
     * Should return false if status is not confirmed
     *
     * @return bool
     */
    public function isAllowedCancel();

    /**
     * Is it allowed to return reservation
     *
     * Should return false if status is not complete
     *
     * @return bool
     */
    public function isAllowedReturn();
}
