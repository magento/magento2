<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

/**
 * @codeCoverageIgnore
 */
class QuoteSearchResults
    extends \Magento\Framework\Api\SearchResults
    implements \Magento\Quote\Api\Data\QuoteSearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\Quote\Api\Data\QuoteInterface[]
     */
    public function getItems()
    {
        return parent::getItems();
    }
}
