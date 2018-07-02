<?php

namespace Magento\Wishlist\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magento\Wishlist\Api\Data\ItemInterface[]
     */
    public function getItems();

    /**
     * @param \Magento\Wishlist\Api\Data\ItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}
