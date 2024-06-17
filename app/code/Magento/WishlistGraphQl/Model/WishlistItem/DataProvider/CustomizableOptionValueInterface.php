<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\WishlistItem\DataProvider;

use Magento\Catalog\Model\Product\Option;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\Item\Option as SelectedOption;

/**
 * Customizable Option Value Data provider
 */
interface CustomizableOptionValueInterface
{
    /**
     * Customizable Option Value Data Provider
     *
     * @param WishlistItem $wishlistItem
     * @param Option $option
     * @param SelectedOption $selectedOption
     * @return array
     */
    public function getData(
        WishlistItem $wishlistItem,
        Option $option,
        SelectedOption $selectedOption
    ): array;
}
