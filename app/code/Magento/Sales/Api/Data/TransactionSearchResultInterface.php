<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Api\Data;

/**
 * Transaction search result interface.
 *
 * A transaction is an interaction between a merchant and a customer such as a purchase, a credit, a refund, and so on.
 * @api
 * @since 100.0.2
 */
interface TransactionSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Magento\Sales\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
