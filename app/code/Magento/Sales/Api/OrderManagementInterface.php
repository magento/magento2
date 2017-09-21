<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Api;

/**
 * Order management interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 100.0.2
 */
interface OrderManagementInterface
{
    /**
     * Cancels a specified order.
     *
     * @param int $id The order ID.
     * @return bool
     */
    public function cancel($id);

    /**
     * Lists comments for a specified order.
     *
     * @param int $id The order ID.
     * @return \Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface Order status history search results interface.
     */
    public function getCommentsList($id);

    /**
     * Adds a comment to a specified order.
     *
     * @param int $id The order ID.
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory Status history comment.
     * @return bool
     */
    public function addComment($id, \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory);

    /**
     * Emails a user a specified order.
     *
     * @param int $id The order ID.
     * @return bool
     */
    public function notify($id);

    /**
     * Gets the status for a specified order.
     *
     * @param int $id The order ID.
     * @return string Order status.
     */
    public function getStatus($id);

    /**
     * Holds a specified order.
     *
     * @param int $id The order ID.
     * @return bool
     */
    public function hold($id);

    /**
     * Releases a specified order from hold status.
     *
     * @param int $id The order ID.
     * @return bool
     */
    public function unHold($id);

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function place(\Magento\Sales\Api\Data\OrderInterface $order);
}
