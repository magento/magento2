<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryConfigurationInterface;

/**
 * Inventory index record interface
 *
 * Used to determine product availability without
 * performing expensive checks on complex data during browsing website
 */
interface InventoryIndexRecordInterface
{
    /**
     * Returns product identifier
     *
     * @return int
     */
    public function getProductId();

    /**
     * Returns inventory identifier
     *
     * @return int
     */
    public function getInventoryId();

    /**
     * Returns true if product is available for purchase
     *
     * @return string
     */
    public function isAvailable();

    /**
     * Returns availability status code
     *
     * Can be useful to show different status massages on frontend, like ships within 2-3 days.
     *
     * @return string
     */
    public function getAvailabilityStatusCode();

    /**
     * Returns available quantity in index
     *
     * This value can be different from actual stock levels, and might be used to promote
     *
     * @return string
     */
    public function getQuantity();

    /**
     * Returns instance of inventory configuration
     *
     * Object should be set by inventory record repository,
     * during instantiation of it
     *
     * @return InventoryConfigurationInterface
     */
    public function getInventoryConfiguration();
}
