<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Shared;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Wishlist\Helper\Data;

class Index extends Action
{
    /**
     * @param Context $context
     * @param WishlistProvider $wishlistProvider
     * @param Registry $registry
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        protected readonly WishlistProvider $wishlistProvider,
        protected ?Registry $registry = null,
        protected readonly Session $customerSession
    ) {
        parent::__construct($context);
    }

    /**
     * Shared wishlist view page
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        $customerId = $this->customerSession->getCustomerId();

        if ($wishlist && $wishlist->getCustomerId() && $wishlist->getCustomerId() == $customerId) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl(
                $this->_objectManager->get(Data::class)->getListUrl($wishlist->getId())
            );
            return $resultRedirect;
        }

        $this->registry->register('shared_wishlist', $wishlist);

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
