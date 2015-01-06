<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice search result interface.
 *
 * An invoice is a record of the receipt of payment for an order.
 */
interface InvoiceSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface[] Array of collection items.
     */
    public function getItems();
}
