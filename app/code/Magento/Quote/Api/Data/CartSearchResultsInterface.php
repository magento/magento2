<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface CartSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_ITEMS = 'items';

    /**#@-*/

    /**
     * Get carts list.
     *
     * @return \Magento\Quote\Api\Data\CartInterface[]
     */
    public function getItems();

    /**
     * Set carts list.
     *
     * @param \Magento\Quote\Api\Data\CartInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
