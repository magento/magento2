<?php

namespace Magento\CatalogInventory\Api;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Request for the inventory records or reservation
 *
 * @api
 */
interface InventoryRequestInterface
{
    /**
     * Returns request identifier it will be used to return matched inventory item
     *
     * The best option to use spl_object_hash
     *
     * @return string
     */
    public function getId();

    /**
     * Returns requested quantity for specified product
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Returns product used for reservation
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Flag for notifying inventory resolver that multiple inventories
     * are allowed for response
     *
     * @return bool
     */
    public function isMultipleInventory();
}
