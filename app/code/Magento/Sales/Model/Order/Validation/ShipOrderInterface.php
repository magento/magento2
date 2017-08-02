<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Interface ShipOrderInterface
 *
 * @api
 * @since 2.2.0
 */
interface ShipOrderInterface
{
    /**
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\ShipmentCommentCreationInterface|null $comment
     * @param array $tracks
     * @param array $packages
     * @param \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface|null $arguments
     * @return \Magento\Sales\Model\ValidatorResultInterface
     * @since 2.2.0
     */
    public function validate(
        $order,
        $shipment,
        array $items = [],
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        array $tracks = [],
        array $packages = [],
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface $arguments = null
    );
}
