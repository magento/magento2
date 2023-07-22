<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Framework\Controller\ResultFactory;

/**
 * Action Add All to Cart
 */
class Allcart extends AbstractIndex implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param Validator $formKeyValidator
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        Context $context,
        protected readonly WishlistProviderInterface $wishlistProvider,
        protected readonly Validator $formKeyValidator,
        protected readonly ItemCarrier $itemCarrier
    ) {
        parent::__construct($context);
    }

    /**
     * Add all items from wishlist to shopping cart
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
