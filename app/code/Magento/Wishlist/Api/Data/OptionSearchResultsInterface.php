<?php

namespace Magento\Wishlist\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface OptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magento\Wishlist\Api\Data\OptionInterface[]
     */
    public function getItems();

    /**
     * @param \Magento\Wishlist\Api\Data\OptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}
