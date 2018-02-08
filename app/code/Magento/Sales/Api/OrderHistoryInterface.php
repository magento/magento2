<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Api;

/**
 * Order status history interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order
 * that lists the product items, billing and shipping addresses, and shipping and payment methods.
 * A corresponding external document, known as a purchase order, is emailed to the customer.
 *
 * @api
 * @since 101.0.2
 */
interface OrderHistoryInterface
{
    /**
     * Get all status history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getAllStatusHistory($order);

    /**
     * Get credit memo history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getCreditMemosHistory($order);

    /**
     * Get shipment history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getShipmentHistory($order);

    /**
     * Get invoice history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getInvoiceHistory($order);

    /**
     * Get tracking history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getTracksHistory($order);

    /**
     * Compose and get order full history.
     * Consists of the status history comments as well as of invoices,
     * shipments and creditmemos creations
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getFullHistory($order);
}
