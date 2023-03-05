<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Class AddToCart
 * @deprecated 101.0.0
 * @package Magento\Wishlist\Observer
 */
class AddToCart implements ObserverInterface
{
    /**
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param WishlistFactory $wishlistFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected readonly CheckoutSession $checkoutSession,
        protected readonly CustomerSession $customerSession,
        protected readonly WishlistFactory $wishlistFactory,
        protected readonly ManagerInterface $messageManager
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $sharedWishlist = $this->checkoutSession->getSharedWishlist();
        $messages = $this->checkoutSession->getWishlistPendingMessages();
        $urls = $this->checkoutSession->getWishlistPendingUrls();
        $wishlistIds = $this->checkoutSession->getWishlistIds();
        $singleWishlistId = $this->checkoutSession->getSingleWishlistId();

        if ($singleWishlistId) {
            $wishlistIds = [$singleWishlistId];
        }

        if (is_array($wishlistIds) && count($wishlistIds) && $request->getParam('wishlist_next')) {
            $wishlistId = array_shift($wishlistIds);

            if ($this->customerSession->isLoggedIn()) {
                $wishlist = $this->wishlistFactory->create()
                    ->loadByCustomerId($this->customerSession->getCustomerId(), true);
            } elseif ($sharedWishlist) {
                $wishlist = $this->wishlistFactory->create()->loadByCode($sharedWishlist);
            } else {
                return;
            }

            $wishlists = $wishlist->getItemCollection()->load();
            foreach ($wishlists as $wishlistItem) {
                if ($wishlistItem->getId() == $wishlistId) {
                    $wishlistItem->delete();
                }
            }
            $this->checkoutSession->setWishlistIds($wishlistIds);
            $this->checkoutSession->setSingleWishlistId(null);
        }

        if ($request->getParam('wishlist_next') && count($urls)) {
            $url = array_shift($urls);
            $message = array_shift($messages);

            $this->checkoutSession->setWishlistPendingUrls($urls);
            $this->checkoutSession->setWishlistPendingMessages($messages);

            $this->messageManager->addError($message);

            $observer->getEvent()->getResponse()->setRedirect($url);
            $this->checkoutSession->setNoCartRedirect(true);
        }
    }
}
