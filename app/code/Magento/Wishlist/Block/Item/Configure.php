<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wishlist Item Configure block
 * Serves for configuring item on product view page
 *
 * @module     Wishlist
 */
namespace Magento\Wishlist\Block\Item;

class Configure extends \Magento\Framework\View\Element\Template
{
    /**
     * Wishlist data
     *
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Helper\Data $wishlistData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistData,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_wishlistData = $wishlistData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Returns product being edited
     *
     * @return \Magento\Catalog\Model\Product
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
     * @return \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item
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
        if ($block) {
            $url = $this->_wishlistData->getAddToCartUrl($this->getWishlistItem());
            $block->setCustomAddToCartUrl($url);
        }

        return parent::_prepareLayout();
    }
}
