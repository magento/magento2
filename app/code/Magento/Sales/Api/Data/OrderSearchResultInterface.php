<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Api\Data;

/**
 * Interface OrderSearchResultInterface
 */
interface OrderSearchResultInterface
{
    /**
     * Get collection items
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface[]
     */
    public function getItems();
}
