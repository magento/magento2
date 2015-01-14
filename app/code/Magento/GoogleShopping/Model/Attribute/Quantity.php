<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Quantity attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model\Attribute;

class Quantity extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     */
    public function convertAttribute($product, $entry)
    {
        $quantity = $product->getStockItem() ? $product->getStockItem()->getQty() : false;
        if ($quantity) {
            $value = $quantity ? max(1, (int)$quantity) : 1;
            $this->_setAttribute($entry, 'quantity', self::ATTRIBUTE_TYPE_INT, $value);
        }
        return $entry;
    }
}
