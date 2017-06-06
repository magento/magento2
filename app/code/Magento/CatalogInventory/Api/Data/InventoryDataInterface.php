<?php

namespace Magento\CatalogInventory\Api\Data;

/**
 * Interface of shared data of inventory with warehouse
 *
 */
interface InventoryDataInterface
    extends LocationInformationInterface,
            StockInterface
{
    /**
     * Returns inventory identifier
     *
     * @return int
     */
    public function getId();

    /**
     * Returns unique inventory code
     *
     * @return string
     */
    public function getCode();
}
