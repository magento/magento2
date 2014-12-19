<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
