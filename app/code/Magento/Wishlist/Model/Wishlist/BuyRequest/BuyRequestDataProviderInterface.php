<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\BuyRequest;

use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;

/**
 * Build buy request for adding products to wishlist
 *
 * @api
 */
interface BuyRequestDataProviderInterface
{
    /**
     * Provide buy request data from add to wishlist item request
     *
     * @param WishlistItem $wishlistItemData
     * @param int|null $productId
     *
     * @return array
     */
    public function execute(WishlistItem $wishlistItemData, ?int $productId): array;
}
