<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Shipment notifier.
 *
 * @api
 * @since 2.1.2
 */
class Notifier implements \Magento\Sales\Model\Order\Shipment\NotifierInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\SenderInterface[]
     * @since 2.1.2
     */
    private $senders;

    /**
     * @param \Magento\Sales\Model\Order\Shipment\SenderInterface[] $senders
     * @since 2.1.2
     */
    public function __construct(array $senders = [])
    {
        $this->senders = $senders;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function notify(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        $forceSyncMode = false
    ) {
        foreach ($this->senders as $sender) {
            $sender->send($order, $shipment, $comment, $forceSyncMode);
        }
    }
}
