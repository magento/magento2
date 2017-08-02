<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Transaction search result interface.
 *
 * A transaction is an interaction between a merchant and a customer such as a purchase, a credit, a refund, and so on.
 * @api
 * @since 2.0.0
 */
interface TransactionSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface[] Array of collection items.
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Magento\Sales\Api\Data\TransactionInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
