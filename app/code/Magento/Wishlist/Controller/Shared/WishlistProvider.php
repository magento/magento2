<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Shared;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

class WishlistProvider implements WishlistProviderInterface
{
    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @param RequestInterface $request
     * @param WishlistFactory $wishlistFactory
     * @param Session $checkoutSession
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly WishlistFactory $wishlistFactory,
        protected readonly Session $checkoutSession
    ) {
    }

    /**
     * Retrieve current wishlist
     * @param string $wishlistId
     * @return Wishlist
     */
    public function getWishlist($wishlistId = null)
    {
        if ($this->wishlist) {
            return $this->wishlist;
        }
        $code = (string)$this->request->getParam('code');
        if (empty($code)) {
            return false;
        }

        $wishlist = $this->wishlistFactory->create()->loadByCode($code);
        if (!$wishlist->getId()) {
            return false;
        }

        $this->checkoutSession->setSharedWishlist($code);
        $this->wishlist = $wishlist;
        return $wishlist;
    }
}
