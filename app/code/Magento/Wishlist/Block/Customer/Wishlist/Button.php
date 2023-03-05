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

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Config;
use Magento\Wishlist\Model\Wishlist;

/**
 * @api
 * @since 100.0.2
 */
class Button extends Template
{
    /**
     * Wishlist config
     *
     * @var Config
     */
    protected $_wishlistConfig;

    /**
     * Wishlist data
     *
     * @var Data
     */
    protected $_wishlistData = null;

    /**
     * @param Context $context
     * @param Data $wishlistData
     * @param Config $wishlistConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $wishlistData,
        Config $wishlistConfig,
        array $data = []
    ) {
        $this->_wishlistData = $wishlistData;
        $this->_wishlistConfig = $wishlistConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current wishlist
     *
     * @return Wishlist
     */
    public function getWishlist()
    {
        return $this->_wishlistData->getWishlist();
    }

    /**
     * Retrieve wishlist config
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->_wishlistConfig;
    }
}
