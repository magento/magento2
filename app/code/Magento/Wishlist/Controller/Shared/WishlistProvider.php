<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Shared;

use Magento\Wishlist\Controller\WishlistProviderInterface;

class WishlistProvider implements WishlistProviderInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->request = $request;
        $this->wishlistFactory = $wishlistFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Retrieve current wishlist
     * @param string $wishlistId
     * @return \Magento\Wishlist\Model\Wishlist
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
