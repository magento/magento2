<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Core\App\Action\FormKeyValidator;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Controller\IndexInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemCarrier;

class Allcart extends Action\Action implements IndexInterface
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier
     */
    protected $itemCarrier;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @param Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param FormKeyValidator $formKeyValidator
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        Context $context,
        WishlistProviderInterface $wishlistProvider,
        FormKeyValidator $formKeyValidator,
        ItemCarrier $itemCarrier
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->formKeyValidator = $formKeyValidator;
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
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->_forward('noroute');
            return;
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $this->_forward('noroute');
            return;
        }
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
