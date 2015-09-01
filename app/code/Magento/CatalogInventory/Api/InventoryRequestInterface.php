<?php

namespace Magento\CatalogInventory\Api;

/**
 * Request for the inventory records
 *
 * @api
 */
interface InventoryRequestInterface
{
    /**
     * Returns identifier of the request
     *
     * @return string
     */
    public function getId();

    /**
     * Returns an associative array of product requested quantities
     * in relation to its identifiers
     *
     * This way of request creation allows to
     *
     * The result looks like the following:
     * [$productId => $productQty]
     *
     * @return string[]
     */
    public function getProductQuantities();

    /**
     * Flag for notifying inventory resolver that multiple inventories
     * are allowed for response
     *
     * @return bool
     */
    public function isMultipleInventory();
}
