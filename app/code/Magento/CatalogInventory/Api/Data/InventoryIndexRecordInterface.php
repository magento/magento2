<?php

namespace Magento\CatalogInventory\Api\Data;

use Magento\CatalogInventory\Api\ConfigurationAwareInterface;

/**
 * Inventory index record interface
 *
 * Used to determine product availability without performing expensive checks on complex data during browsing website
 */
interface InventoryIndexRecordInterface extends InventoryRecordDataInterface, ConfigurationAwareInterface
{
    /**
     * Returns true if product is available for purchase
     *
     * @return string
     */
    public function isAvailable();
}
