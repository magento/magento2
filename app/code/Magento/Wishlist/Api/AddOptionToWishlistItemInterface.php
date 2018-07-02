<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Api;

interface AddOptionToWishlistItemInterface
{
    /**
     * @param Data\ItemInterface $item
     * @param Data\OptionInterface $option
     * @return Data\ItemInterface
     */
    public function execute(
        \Magento\Wishlist\Api\Data\ItemInterface $item,
        \Magento\Wishlist\Api\Data\OptionInterface $option
    ): \Magento\Wishlist\Api\Data\ItemInterface;
}
