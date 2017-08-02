<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice item search result interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice item is a purchased item in an invoice.
 * @api
 * @since 2.0.0
 */
interface InvoiceItemSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[] Array of collection items.
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
