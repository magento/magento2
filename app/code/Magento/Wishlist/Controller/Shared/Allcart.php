<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Controller\Shared;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Wishlist\Model\ItemCarrier;

class Allcart implements HttpGetActionInterface
{
    /**
     * @var ItemCarrier
     */
    private $itemCarrier;

    /**
     * @var WishlistProvider
     */
    private $wishlistProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param ItemCarrier $itemCarrier
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param WishlistProvider $wishlistProvider
     */
    public function __construct(
        ItemCarrier $itemCarrier,
        RequestInterface $request,
        ResultFactory $resultFactory,
        WishlistProvider $wishlistProvider
    ) {
        $this->itemCarrier = $itemCarrier;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * Add all items from wishlist to shopping cart
     *
     * {@inheritDoc}
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->request->getParam('qty'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
