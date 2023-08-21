<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Adminhtml\ResourceModel\Item\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemCollection;
use Magento\Wishlist\Model\ResourceModel\Item\Product\CollectionBuilderInterface;

/**
 * Wishlist items products collection builder for adminhtml area
 */
class CollectionBuilder implements CollectionBuilderInterface
{
    /**
     * @inheritDoc
     */
    public function build(WishlistItemCollection $wishlistItemCollection, Collection $productCollection): Collection
    {
        return $productCollection;
    }
}
