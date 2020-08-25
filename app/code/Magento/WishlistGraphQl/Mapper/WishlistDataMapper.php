<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Mapper;

use Magento\Wishlist\Model\Wishlist;

/**
 * Prepares the wishlist output as associative array
 */
class WishlistDataMapper
{
    /**
     * Mapping the review data
     *
     * @param Wishlist $wishlist
     *
     * @return array
     */
    public function map(Wishlist $wishlist): array
    {
        return [
            'id' => $wishlist->getId(),
            'sharing_code' => $wishlist->getSharingCode(),
            'updated_at' => $wishlist->getUpdatedAt(),
            'items_count' => $wishlist->getItemsCount(),
            'name' => $wishlist->getName(),
            'model' => $wishlist,
        ];
    }
}
