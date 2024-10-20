<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemCollection;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Fetches the Wishlist Items data according to the GraphQL schema
 */
class WishlistItemsResolver implements ResolverInterface
{
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var WishlistItemCollectionFactory
     */
    private $wishlistItemCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param WishlistItemCollectionFactory $wishlistItemCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param WishlistFactory $wishlistFactory
     */
    public function __construct(
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        StoreManagerInterface $storeManager,
        WishlistFactory $wishlistFactory
    ) {
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->storeManager = $storeManager;
        $this->wishlistFactory = $wishlistFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            $value['model'] = $this->wishlistFactory->create();
        }
        /** @var Wishlist $wishlist */
        $wishlist = $value['model'];

        $wishlistItems = $this->getWishListItems($wishlist);

        $data = [];
        foreach ($wishlistItems as $wishlistItem) {
            $data[] = [
                'id' => $wishlistItem->getId(),
                'qty' => $wishlistItem->getData('qty'),
                'description' => $wishlistItem->getDescription(),
                'added_at' => $wishlistItem->getAddedAt(),
                'model' => $wishlistItem->getProduct(),
            ];
        }
        return $data;
    }

    /**
     * Get wishlist items
     *
     * @param Wishlist $wishlist
     * @return Item[]
     */
    private function getWishListItems(Wishlist $wishlist): array
    {
        /** @var WishlistItemCollection $wishlistItemCollection */
        $wishlistItemCollection = $this->wishlistItemCollectionFactory->create();
        $wishlistItemCollection
            ->addWishlistFilter($wishlist)
            ->addStoreFilter(array_map(function (StoreInterface $store) {
                return $store->getId();
            }, $this->storeManager->getStores()))
            ->setVisibilityFilter();
        return $wishlistItemCollection->getItems();
    }
}
