<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemCollection;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;

/**
 * Fetches the Wishlist Items data according to the GraphQL schema
 */
class WishlistItems implements ResolverInterface
{
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
     */
    public function __construct(
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->storeManager = $storeManager;
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
            throw new LocalizedException(__('Missing key "model" in Wishlist value data'));
        }
        /** @var Wishlist $wishlist */
        $wishlist = $value['model'];

        /** @var WishlistItemCollection $wishlistItemCollection */
        $wishlistItemsCollection = $this->getWishListItems($wishlist, $args);
        $wishlistItems = $wishlistItemsCollection->getItems();

        $data = [];
        foreach ($wishlistItems as $wishlistItem) {
            $data[] = [
                'id' => $wishlistItem->getId(),
                'quantity' => $wishlistItem->getData('qty'),
                'description' => $wishlistItem->getDescription(),
                'added_at' => $wishlistItem->getAddedAt(),
                'model' => $wishlistItem->getProduct(),
                'itemModel' => $wishlistItem,
            ];
        }
        return [
            'items' => $data,
            'page_info' => [
                'current_page' => $wishlistItemsCollection->getCurPage(),
                'page_size' => $wishlistItemsCollection->getPageSize(),
                'total_pages' => $wishlistItemsCollection->getLastPageNumber()
            ]
        ];
    }

    /**
     * Get wishlist items
     *
     * @param Wishlist $wishlist
     * @param array $args
     * @return WishlistItemCollection
     */
    private function getWishListItems(Wishlist $wishlist, array $args): WishlistItemCollection
    {
        $currentPage = $args['currentPage'] ?? 1;
        $pageSize = $args['pageSize'] ?? 20;

        /** @var WishlistItemCollection $wishlistItemCollection */
        $wishlistItemCollection = $this->wishlistItemCollectionFactory->create();
        $wishlistItemCollection
            ->addWishlistFilter($wishlist)
            ->addStoreFilter(array_map(function (StoreInterface $store) {
                return $store->getId();
            }, $this->storeManager->getStores()))
            ->setVisibilityFilter();
        if ($currentPage > 0) {
            $wishlistItemCollection->setCurPage($currentPage);
        }

        if ($pageSize > 0) {
            $wishlistItemCollection->setPageSize($pageSize);
        }
        return $wishlistItemCollection;
    }
}
