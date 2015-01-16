<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

/**
 * @codeCoverageIgnore
 */
class QuoteSearchResults extends \Magento\Framework\Api\SearchResults implements
    \Magento\Quote\Api\Data\CartSearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\Quote\Api\Data\CartInterface[]
     */
    public function getItems()
    {
        return parent::getItems();
    }
}
