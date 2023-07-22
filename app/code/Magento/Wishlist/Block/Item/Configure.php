<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist Item Configure block
 * Serves for configuring item on product view page
 *
 * @module     Wishlist
 */
namespace Magento\Wishlist\Block\Item;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;

/**
 * @api
 * @since 100.0.2
 */
class Configure extends Template
{
    /**
     * Wishlist data
     *
     * @var Data
     */
    protected $_wishlistData = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Data $wishlistData
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $wishlistData,
        Registry $registry,
        array $data = []
    ) {
        $this->_wishlistData = $wishlistData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return wishlist widget options
     *
     * @return array
     */
    public function getWishlistOptions()
    {
        return ['productType' => $this->escapeHtml($this->getProduct()->getTypeId())];
    }

    /**
     * Returns product being edited
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get update params for http post
     *
     * @return bool|string
     */
    public function getUpdateParams()
    {
        return $this->_wishlistData->getUpdateParams($this->getWishlistItem());
    }

    /**
     * Returns wishlist item being configured
     *
     * @return Product|Item
     */
    protected function getWishlistItem()
    {
        return $this->_coreRegistry->registry('wishlist_item');
    }

    /**
     * Configure product view blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        // Set custom add to cart url
        $block = $this->getLayout()->getBlock('product.info');
        if ($block && $this->getWishlistItem()) {
            $url = $this->_wishlistData->getAddToCartUrl($this->getWishlistItem());
            $block->setCustomAddToCartUrl($url);
        }

        return parent::_prepareLayout();
    }
}
