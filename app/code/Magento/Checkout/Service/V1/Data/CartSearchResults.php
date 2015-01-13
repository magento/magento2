<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data;

/**
 * @codeCoverageIgnore
 */
class CartSearchResults extends \Magento\Framework\Api\SearchResults
{
    /**
     * Get items
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart[]
     */
    public function getItems()
    {
        return parent::getItems();
    }
}
