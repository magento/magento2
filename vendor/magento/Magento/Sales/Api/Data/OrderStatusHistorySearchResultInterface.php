<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Order status history search result interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 */
interface OrderStatusHistorySearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\OrderStatusHistoryInterface[] Array of collection items.
     */
    public function getItems();
}
