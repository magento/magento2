<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order search result interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 2.0.0
 */
interface OrderSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface[] Array of collection items.
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null);
}
