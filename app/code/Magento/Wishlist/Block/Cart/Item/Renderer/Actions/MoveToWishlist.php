<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Template;
use Magento\Wishlist\Helper\Data;

/**
 * @api
 * @since 2.0.0
 */
class MoveToWishlist extends Generic
{
    /**
     * @var Data
     * @since 2.0.0
     */
    protected $wishlistHelper;

    /**
     * @param Template\Context $context
     * @param Data $wishlistHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        Data $wishlistHelper,
        array $data = []
    ) {
        $this->wishlistHelper = $wishlistHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check whether "add to wishlist" button is allowed in cart
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAllowInCart()
    {
        return $this->wishlistHelper->isAllowInCart();
    }

    /**
     * Get JSON POST params for moving from cart
     *
     * @return string
     * @since 2.0.0
     */
    public function getMoveFromCartParams()
    {
        return $this->wishlistHelper->getMoveFromCartParams($this->getItem()->getId());
    }
}
