<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;

class WishlistItemsDataProvider
{

    /**
     * @var WishlistItemCollectionFactory
     */
    private $wishlistItemCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $customerId
     * @return Item[]
     */
    public function getWishlistItemsForCustomer(int $customerId): array
    {
        $wishlistItemCollection = $this->wishlistItemCollectionFactory->create();
        $wishlistItemCollection->addCustomerIdFilter($customerId);
        $wishlistItemCollection->addStoreFilter(array_map(function (StoreInterface $store) {
            return $store->getId();
        }, $this->storeManager->getStores()));
        $wishlistItemCollection->setVisibilityFilter();
        $wishlistItemCollection->load();
        return $wishlistItemCollection->getItems();
    }
}
