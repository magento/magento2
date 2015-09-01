<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Inventory provider
 *
 * It can use scope configuration to retrieve a default inventory,
 * customer data to retrieve inventory based on customer location, etc
 *
 * @api
 */
interface InventoryProviderInterface
{
    /**
     * Returns inventory based on scope configuration
     *
     * Also can have other dependencies specified via constructor,
     * as customer location, etc.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @return InventoryInterface
     */
    public function getInventory(ScopeConfigInterface $scopeConfig);
}
