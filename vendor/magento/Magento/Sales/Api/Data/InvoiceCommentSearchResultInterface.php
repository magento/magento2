<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice comment search result interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice can include comments that detail the
 * invoice history.
 */
interface InvoiceCommentSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface[] Array of collection items.
     */
    public function getItems();
}
