<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
