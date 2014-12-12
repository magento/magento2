<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Api\Data;

/**
 * Interface OrderAddressSearchResultInterface
 */
interface OrderAddressSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get collection items
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface[]
     */
    public function getItems();
}
