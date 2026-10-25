<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Shopping Cart Rule data model
 */
namespace Magento\OfflineShipping\Model\SalesRule;

/**
 * @api
 * @since 100.0.2
 */
class Rule
{
    /**
     * Free Shipping option "For matching items only"
     */
    public const FREE_SHIPPING_ITEM = 1;

    /**
     * Free Shipping option "For shipment with matching items"
     */
    public const FREE_SHIPPING_ADDRESS = 2;
}
