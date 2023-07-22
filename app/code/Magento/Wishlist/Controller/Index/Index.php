<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;

class Index extends AbstractIndex
{
    /**
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     */
    public function __construct(
        Action\Context $context,
        protected readonly WishlistProviderInterface $wishlistProvider
    ) {
        parent::__construct($context);
    }

    /**
     * Display customer wishlist
     *
     * @return Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->wishlistProvider->getWishlist()) {
            throw new NotFoundException(__('Page not found.'));
        }
        /** @var Page resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
