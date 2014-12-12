<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ShipmentSearchResultInterface
 */
interface ShipmentSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get collection items
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface[]
     */
    public function getItems();
}
