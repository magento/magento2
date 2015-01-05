<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
