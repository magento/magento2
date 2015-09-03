<?php

namespace Magento\CatalogInventory\Api\Data;

use Magento\CatalogInventory\Api\LocationInformationInterface;

/**
 * Interface of shared data of inventory with warehouse
 *
 */
interface InventoryDataInterface extends LocationInformationInterface
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
}
