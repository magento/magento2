<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\ResourceModel\Item\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemCollection;

/**
 * Wishlist items products collection builder
 *
 * @api
 */
interface CollectionBuilderInterface
{
    /**
     * Modify product collection
     *
     * @param WishlistItemCollection $wishlistItemCollection
     * @param Collection $productCollection
     * @return Collection
     */
    public function build(WishlistItemCollection $wishlistItemCollection, Collection $productCollection): Collection;
}
