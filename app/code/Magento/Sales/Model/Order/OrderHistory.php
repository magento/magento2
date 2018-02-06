<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\OrderHistoryInterface;

/**
 * Class OrderHistory
 * @package Magento\Sales\Model\Order
 */
class OrderHistory implements OrderHistoryInterface
{
    /**
     * Map history items as array
     *
     * @param string $label
     * @param bool $notified
     * @param \DateTimeInterface $created
     * @param string $comment
     * @return array
     */
    protected function _prepareHistoryItem($label, $notified, $created, $comment = '')
    {
        return ['title' => $label, 'notified' => $notified, 'comment' => $comment, 'created_at' => $created];
    }

    /**
     * Get all status history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getAllStatusHistory($order)
    {
        $history = [];
        foreach ($order->getAllStatusHistory() as $orderComment) {
            $history[] = $this->_prepareHistoryItem(
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified(),
                $orderComment->getCreatedAt(),
                $orderComment->getComment()
            );
        }

        return $history;
    }

    /**
     * Get credit memo history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getCreditMemosHistory($order)
    {
        $history = [];
        foreach ($order->getCreditmemosCollection() as $_memo) {
            $history[] = $this->_prepareHistoryItem(
                __('Credit memo #%1 created', $_memo->getIncrementId()),
                $_memo->getEmailSent(),
                $_memo->getCreatedAt()
            );

            foreach ($_memo->getCommentsCollection() as $_comment) {
                $history[] = $this->_prepareHistoryItem(
                    __('Credit memo #%1 comment added', $_memo->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAt(),
                    $_comment->getComment()
                );
            }
        }

        return $history;
    }

    /**
     * Get shipment history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getShipmentHistory($order)
    {
        $history = [];
        foreach ($order->getShipmentsCollection() as $_shipment) {
            $history[] = $this->_prepareHistoryItem(
                __('Shipment #%1 created', $_shipment->getIncrementId()),
                $_shipment->getEmailSent(),
                $_shipment->getCreatedAt()
            );

            foreach ($_shipment->getCommentsCollection() as $_comment) {
                $history[] = $this->_prepareHistoryItem(
                    __('Shipment #%1 comment added', $_shipment->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAt(),
                    $_comment->getComment()
                );
            }
        }

        return $history;
    }

    /**
     * Get invoice history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getInvoiceHistory($order)
    {
        $history = [];
        foreach ($order->getInvoiceCollection() as $_invoice) {
            $history[] = $this->_prepareHistoryItem(
                __('Invoice #%1 created', $_invoice->getIncrementId()),
                $_invoice->getEmailSent(),
                $_invoice->getCreatedAt()
            );

            foreach ($_invoice->getCommentsCollection() as $_comment) {
                $history[] = $this->_prepareHistoryItem(
                    __('Invoice #%1 comment added', $_invoice->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAt(),
                    $_comment->getComment()
                );
            }
        }

        return $history;
    }

    /**
     * Get tracking history data for specific order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getTracksHistory($order)
    {
        $history = [];
        foreach ($order->getTracksCollection() as $_track) {
            $history[] = $this->_prepareHistoryItem(
                __('Tracking number %1 for %2 assigned', $_track->getNumber(), $_track->getTitle()),
                false,
                $_track->getCreatedAt()
            );
        }

        return $history;
    }

    /**
     * Compose and get order full history.
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getFullHistory($order)
    {
        $history = [];
        $history = array_merge($history, $this->getAllStatusHistory($order));
        $history = array_merge($history, $this->getCreditMemosHistory($order));
        $history = array_merge($history, $this->getShipmentHistory($order));
        $history = array_merge($history, $this->getInvoiceHistory($order));
        $history = array_merge($history, $this->getTracksHistory($order));

        return $history;
    }
}
