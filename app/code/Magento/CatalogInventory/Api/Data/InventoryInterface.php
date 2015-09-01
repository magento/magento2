<?php

namespace Magento\CatalogInventory\Api\Data;

use Magento\CatalogInventory\Api\LocationInformationInterface;
use Magento\CatalogInventory\Api\ConfigurationInterface;

/**
 * Inventory interface
 *
 * @api
 */
interface InventoryInterface extends LocationInformationInterface
{
    /**
     * Returns inventory identifier
     *
     * @return int
     */
    public function getId();

    /**
     * Returns inventory name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns unique inventory code
     *
     * @return string
     */
    public function getCode();

    /**
     * Returns inventory configuration object
     *
     * Object should be set from inventory repository, after inventory is instantiated,
     * so additional configuration overrides of inventory going to be applied as well
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * Returns true if this inventory should be indexed
     *
     * @return bool
     */
    public function isIndexed();

    /**
     * Returns true if this inventory is virtual
     *
     * Used to restrict purchase of items from it,
     * as it is might be just an aggregated inventory
     *
     * @return bool
     */
    public function isVirtual();
}
