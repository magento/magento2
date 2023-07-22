<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Controller\Index;

use Exception;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

/**
 * Add cart item to wishlist and remove from cart controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Fromcart extends AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param WishlistHelper $wishlistHelper
     * @param CheckoutCart $cart
     * @param CartHelper $cartHelper
     * @param Escaper $escaper
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        protected readonly WishlistProviderInterface $wishlistProvider,
        protected readonly WishlistHelper $wishlistHelper,
        protected readonly CheckoutCart $cart,
        protected readonly CartHelper $cartHelper,
        protected readonly Escaper $escaper,
        protected readonly Validator $formKeyValidator
    ) {
        parent::__construct($context);
    }

    /**
     * Add cart item to wishlist and remove from cart
     *
     * @return Redirect
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        try {
            $itemId = (int)$this->getRequest()->getParam('item');
            $item = $this->cart->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new LocalizedException(
                    __("The cart item doesn't exist.")
                );
            }

            $productId = $item->getProductId();
            $buyRequest = $item->getBuyRequest();
            $wishlist->addNewItem($productId, $buyRequest);

            $this->cart->getQuote()->removeItem($itemId);
            $this->cart->save();

            $this->wishlistHelper->calculate();
            $wishlist->save();

            $this->messageManager->addSuccessMessage(__(
                "%1 has been moved to your wish list.",
                $this->escaper->escapeHtml($item->getProduct()->getName())
            ));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t move the item to the wish list.'));
        }
        return $resultRedirect->setUrl($this->cartHelper->getCartUrl());
    }
}
