<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Shopping Cart Rule data model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\OfflineShipping\Model\SalesRule;

class Rule
{
    /**
     * Free Shipping option "For matching items only"
     */
    const FREE_SHIPPING_ITEM = 1;

    /**
     * Free Shipping option "For shipment with matching items"
     */
    const FREE_SHIPPING_ADDRESS = 2;
}
