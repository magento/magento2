<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block customer item cart column
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer\Wishlist;

/**
 * @api
 * @since 2.0.0
 */
class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * Wishlist config
     *
     * @var \Magento\Wishlist\Model\Config
     * @since 2.0.0
     */
    protected $_wishlistConfig;

    /**
     * Wishlist data
     *
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $_wishlistData = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Helper\Data $wishlistData
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistData,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        array $data = []
    ) {
        $this->_wishlistData = $wishlistData;
        $this->_wishlistConfig = $wishlistConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current wishlist
     *
     * @return \Magento\Wishlist\Model\Wishlist
     * @since 2.0.0
     */
    public function getWishlist()
    {
        return $this->_wishlistData->getWishlist();
    }

    /**
     * Retrieve wishlist config
     *
     * @return \Magento\Wishlist\Model\Config
     * @since 2.0.0
     */
    public function getConfig()
    {
        return $this->_wishlistConfig;
    }
}
