<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\ViewModel;

use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Wishlist Url
 */
class Sidebar implements ArgumentInterface
{
    /**
     * @var WishlistHelper
     */
    private $wishlistHelper;

    /**
     * @param WishlistHelper $wishlistHelper
     */
    public function __construct(WishlistHelper $wishlistHelper)
    {
        $this->wishlistHelper = $wishlistHelper;
    }

    /**
     * Get Wishlist URL
     *
     * @return string
     */
    public function getWishlistUrl()
    {
        return $this->wishlistHelper->getListUrl();
    }

    /**
     * Check whether the Wishlist is allowed
     *
     * @return bool
     */
    public function isWishlistAllowed()
    {
        return $this->wishlistHelper->isAllow();
    }
}
