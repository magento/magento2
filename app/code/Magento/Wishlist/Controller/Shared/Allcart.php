<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Shared;

use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Model\ItemCarrier;

class Allcart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier
     */
    protected $itemCarrier;

    /**
     * @param Context $context
     * @param WishlistProvider $wishlistProvider
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        Context $context,
        WishlistProvider $wishlistProvider,
        ItemCarrier $itemCarrier
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->itemCarrier = $itemCarrier;
        parent::__construct($context);
    }

    /**
     * Add all items from wishlist to shopping cart
     *
     * @return void
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $this->_forward('noroute');
            return;
        }
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
