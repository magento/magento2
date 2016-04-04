<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Credit memo item search result interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases. A credit memo item is an invoiced item for which
 * a merchant creates a credit memo.
 * @api
 */
interface CreditmemoItemSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
