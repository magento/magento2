<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Wishlist\Model;

use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Load wish list by customer id.
 */
class GetWishlistByCustomerId
{
    /** @var WishlistFactory */
    private $wishlistFactory;

    /**
     * @param WishlistFactory $wishlistFactory
     */
    public function __construct(WishlistFactory $wishlistFactory)
    {
        $this->wishlistFactory = $wishlistFactory;
    }

    /**
     * Load wish list by customer id.
     *
     * @param int $customerId
     * @return Wishlist
     */
    public function execute(int $customerId): Wishlist
    {
        return $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
    }

    /**
     * Get wish list item by sku.
     *
     * @param int $customerId
     * @param string $sku
     * @return null|Item
     */
    public function getItemBySku(int $customerId, string $sku): ?Item
    {
        $result = null;
        $items = $this->execute($customerId)->getItemCollection()->getItems();
        foreach ($items as $item) {
            if ($item->getProduct()->getData('sku') === $sku) {
                $result = $item;
                break;
            }
        }

        return $result;
    }
}
