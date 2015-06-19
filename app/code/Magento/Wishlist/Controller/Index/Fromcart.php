<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Controller\IndexInterface;
use Magento\Framework\Controller\ResultFactory;

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
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');

        try {
            $item = $cart->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new LocalizedException(
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
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t move the item to the wish list.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl());
    }
}
