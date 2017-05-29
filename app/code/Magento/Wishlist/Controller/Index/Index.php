<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Wishlist\Controller\AbstractIndex
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
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->wishlistProvider->getWishlist()) {
            throw new NotFoundException(__('Page not found.'));
        }
        /** @var \Magento\Framework\View\Result\Page resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
