<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\NotFoundException;
use Magento\Wishlist\Controller\IndexInterface;

class Index extends Action\Action implements IndexInterface
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @param Action\Context $context
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     */
    public function __construct(
        Action\Context $context,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
    ) {
        $this->wishlistProvider = $wishlistProvider;
        parent::__construct($context);
    }

    /**
     * Display customer wishlist
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->wishlistProvider->getWishlist()) {
            throw new NotFoundException();
        }
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
