<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Shared;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param WishlistProvider $wishlistProvider
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        WishlistProvider $wishlistProvider,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Shared wishlist view page
     *
     * @return void
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        $customerId = $this->customerSession->getCustomerId();

        if ($wishlist && $wishlist->getCustomerId() && $wishlist->getCustomerId() == $customerId) {
            $this->getResponse()->setRedirect(
                $this->_objectManager->get('Magento\Wishlist\Helper\Data')->getListUrl($wishlist->getId())
            );
            return;
        }

        $this->registry->register('shared_wishlist', $wishlist);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
