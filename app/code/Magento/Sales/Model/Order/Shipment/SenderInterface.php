<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Interface for notification sender for Shipment.
 */
interface SenderInterface
{
    /**
     * Sends notification to a customer.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Magento\Sales\Api\Data\ShipmentCommentCreationInterface|null $comment
     * @param bool $forceSyncMode
     *
     * @return bool
     */
    public function send(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        $forceSyncMode = false
    );
}
