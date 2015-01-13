<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api\Data;

interface CartSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get carts list.
     *
     * @return \Magento\Checkout\Api\Data\CartInterface[]
     */
    public function getItems();
}
