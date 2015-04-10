<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Wishlist\Controller\IndexInterface;

class Fromcart extends Action\Action implements IndexInterface
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
     * Add cart item to wishlist and remove from cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }
        $itemId = (int)$this->getRequest()->getParam('item');

        /* @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $this->_objectManager->get('Magento\Checkout\Model\Session');

        $item = $cart->getQuote()->getItemById($itemId);
        if (!$item) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The requested cart item doesn\'t exist.')
            );
        }

        $productId = $item->getProductId();
        $buyRequest = $item->getBuyRequest();

        $wishlist->addNewItem($productId, $buyRequest);

        $productIds[] = $productId;
        $cart->getQuote()->removeItem($itemId);
        $cart->save();
        $this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();
        $productName = $this->_objectManager->get('Magento\Framework\Escaper')
            ->escapeHtml($item->getProduct()->getName());
        $wishlistName = $this->_objectManager->get('Magento\Framework\Escaper')
            ->escapeHtml($wishlist->getName());
        $this->messageManager->addSuccess(__("%1 has been moved to wish list %2", $productName, $wishlistName));
        $wishlist->save();

        return $this->getDefaultResult();
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl());
    }
}
