<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Interface for Shipment notifier.
 *
 * @api
 * @since 100.1.2
 */
interface NotifierInterface
{
    /**
     * Notifies customer.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Magento\Sales\Api\Data\ShipmentCommentCreationInterface|null $comment
     * @param bool $forceSyncMode
     *
     * @return void
     * @since 100.1.2
     */
    public function notify(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        $forceSyncMode = false
    );
}
