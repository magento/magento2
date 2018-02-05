<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as ResourceWishlist;

/**
 * Class CartUpdateBefore
 * @package Magento\Wishlist\Observer
 */
class CartUpdateBefore implements ObserverInterface
{
    /**
     * Wishlist data
     *
     * @var Data
     */
    protected $wishlistData;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Wishlist
     */
    protected $resourceModel;

    /**
     * @param Data $wishlistData
     * @param WishlistFactory $wishlistFactory
     * @param \Magento\Wishlist\Model\ResourceModel\Wishlist $resourceModel
     */
    public function __construct(
        Data $wishlistData,
        WishlistFactory $wishlistFactory,
        ResourceWishlist $resourceModel = null
    ) {
        $this->wishlistData = $wishlistData;
        $this->wishlistFactory = $wishlistFactory;
        $this->resourceModel = $resourceModel ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Wishlist::class);
    }

    /**
     * Get customer wishlist model instance
     *
     * @param   int $customerId
     * @return  Wishlist|false
     */
    protected function getWishlist($customerId)
    {
        if (!$customerId) {
            return false;
        }
        return $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
    }

    /**
     * Check move quote item to wishlist request
     *
     * @param   Observer $observer
     * @return  $this
     */
    public function execute(Observer $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo()->toArray();
        $productIds = [];

        $wishlist = $this->getWishlist($cart->getQuote()->getCustomerId());
        if (!$wishlist) {
            return $this;
        }

        /**
         * Collect product ids marked for move to wishlist
         */
        foreach ($data as $itemId => $itemInfo) {
            if (!empty($itemInfo['wishlist']) && ($item = $cart->getQuote()->getItemById($itemId))) {
                $productId = $item->getProductId();
                $buyRequest = $item->getBuyRequest();

                if (array_key_exists('qty', $itemInfo) && is_numeric($itemInfo['qty'])) {
                    $buyRequest->setQty($itemInfo['qty']);
                }
                $wishlist->addNewItem($productId, $buyRequest);

                $productIds[] = $productId;
                $cart->getQuote()->removeItem($itemId);
            }
        }

        if (count($productIds)) {
            $this->resourceModel->save($wishlist);
            $this->wishlistData->calculate();
        }
        return $this;
    }
}
