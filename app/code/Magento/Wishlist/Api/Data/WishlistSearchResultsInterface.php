<?php

namespace Magento\Wishlist\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface WishlistSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magento\Wishlist\Api\Data\WishlistInterface[]
     */
    public function getItems();

    /**
     * @param \Magento\Wishlist\Api\Data\WishlistInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
