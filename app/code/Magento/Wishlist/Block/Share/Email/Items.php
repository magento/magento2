<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block customer items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Share\Email;

class Items extends \Magento\Wishlist\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Wishlist::email/items.phtml';

    /**
     * Retrieve Product View URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        $additional['_scope_to_url'] = true;
        return parent::getProductUrl($product, $additional);
    }

    /**
     * Retrieve URL for add product to shopping cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        $additional['nocookie'] = 1;
        $additional['_scope_to_url'] = true;
        return parent::getAddToCartUrl($product, $additional);
    }

    /**
     * Check whether wishlist item has description
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return bool
     */
    public function hasDescription($item)
    {
        $hasDescription = parent::hasDescription($item);
        if ($hasDescription) {
            return $item->getDescription() !== $this->_wishlistHelper->defaultCommentString();
        }
        return $hasDescription;
    }
}
