<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class ItemCarrier
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirector;

    /**
     * @param Session $customerSession
     * @param LocaleQuantityProcessor $quantityProcessor
     * @param Cart $cart
     * @param Logger $logger
     * @param WishlistHelper $helper
     * @param CartHelper $cartHelper
     * @param UrlInterface $urlBuilder
     * @param MessageManager $messageManager
     * @param RedirectInterface $redirector
     */
    public function __construct(
        Session $customerSession,
        LocaleQuantityProcessor $quantityProcessor,
        Cart $cart,
        Logger $logger,
        WishlistHelper $helper,
        CartHelper $cartHelper,
        UrlInterface $urlBuilder,
        MessageManager $messageManager,
        RedirectInterface $redirector
    ) {
        $this->customerSession = $customerSession;
        $this->quantityProcessor = $quantityProcessor;
        $this->cart = $cart;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->cartHelper = $cartHelper;
        $this->urlBuilder = $urlBuilder;
        $this->messageManager = $messageManager;
        $this->redirector = $redirector;
    }

    /**
     * Move all wishlist item to cart
     *
     * @param Wishlist $wishlist
     * @param array $qtys
     * @return string
     */
    public function moveAllToCart(Wishlist $wishlist, $qtys)
    {
        $isOwner = $wishlist->isOwner($this->customerSession->getCustomerId());

        $messages = [];
        $addedItems = [];
        $notSalable = [];
        $hasOptions = [];

        $cart = $this->cart;
        $collection = $wishlist->getItemCollection()->setVisibilityFilter();

        foreach ($collection as $item) {
            /** @var \Magento\Wishlist\Model\Item */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();

                // Set qty
                if (isset($qtys[$item->getId()])) {
                    $qty = $this->quantityProcessor->process($qtys[$item->getId()]);
                    if ($qty) {
                        $item->setQty($qty);
                    }
                }
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                // Add to cart
                if ($item->addToCart($cart, $isOwner)) {
                    $addedItems[] = $item->getProduct();
                }
            } catch (\Magento\Framework\Model\Exception $e) {
                if ($e->getCode() == \Magento\Wishlist\Model\Item::EXCEPTION_CODE_NOT_SALABLE) {
                    $notSalable[] = $item;
                } elseif ($e->getCode() == \Magento\Wishlist\Model\Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                    $hasOptions[] = $item;
                } else {
                    $messages[] = __('%1 for "%2".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }

                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                if ($cartItem) {
                    $cart->getQuote()->deleteItem($cartItem);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $messages[] = __('We cannot add this item to your shopping cart.');
            }
        }

        if ($isOwner) {
            $indexUrl = $this->helper->getListUrl($wishlist->getId());
        } else {
            $indexUrl = $this->urlBuilder->getUrl('wishlist/shared', ['code' => $wishlist->getSharingCode()]);
        }
        if ($this->cartHelper->getShouldRedirectToCart()) {
            $redirectUrl = $this->cartHelper->getCartUrl();
        } elseif ($this->redirector->getRefererUrl()) {
            $redirectUrl = $this->redirector->getRefererUrl();
        } else {
            $redirectUrl = $indexUrl;
        }

        if ($notSalable) {
            $products = [];
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = __(
                'We couldn\'t add the following product(s) to the shopping cart: %1.',
                join(', ', $products)
            );
        }

        if ($hasOptions) {
            $products = [];
            foreach ($hasOptions as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = __(
                'Product(s) %1 have required options. Each product can only be added individually.',
                join(', ', $products)
            );
        }

        if ($messages) {
            $isMessageSole = count($messages) == 1;
            if ($isMessageSole && count($hasOptions) == 1) {
                $item = $hasOptions[0];
                if ($isOwner) {
                    $item->delete();
                }
                $redirectUrl = $item->getProductUrl();
            } else {
                foreach ($messages as $message) {
                    $this->messageManager->addError($message);
                }
                $redirectUrl = $indexUrl;
            }
        }

        if ($addedItems) {
            // save wishlist model for setting date of last update
            try {
                $wishlist->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t update wish list.'));
                $redirectUrl = $indexUrl;
            }

            $products = [];
            foreach ($addedItems as $product) {
                $products[] = '"' . $product->getName() . '"';
            }

            $this->messageManager->addSuccess(
                __('%1 product(s) have been added to shopping cart: %2.', count($addedItems), join(', ', $products))
            );

            // save cart and collect totals
            $cart->save()->getQuote()->collectTotals();
        }
        $this->helper->calculate();
        return $redirectUrl;
    }
}
