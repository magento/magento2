<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface CartSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get carts list.
     *
     * @return \Magento\Quote\Api\Data\CartInterface[]
     */
    public function getItems();
}
