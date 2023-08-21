<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order;

/**
 * Resolve shipment information for order
 */
class Shipments implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Order $order */
        $order = $value['model'];
        $shipments = $order->getShipmentsCollection()->getItems();

        if (empty($shipments)) {
            //Order does not have any shipments
            return [];
        }

        $orderShipments = [];
        foreach ($shipments as $shipment) {
            $orderShipments[] =
                [
                    'id' => base64_encode($shipment->getIncrementId()),
                    'number' => $shipment->getIncrementId(),
                    'comments' => $this->getShipmentComments($shipment),
                    'model' => $shipment,
                    'order' => $order
                ];
        }
        return $orderShipments;
    }

    /**
     * Get comments shipments in proper format
     *
     * @param ShipmentInterface $shipment
     * @return array
     */
    private function getShipmentComments(ShipmentInterface $shipment): array
    {
        $comments = [];
        foreach ($shipment->getComments() as $comment) {
            if ($comment->getIsVisibleOnFront()) {
                $comments[] = [
                    'timestamp' => $comment->getCreatedAt(),
                    'message' => $comment->getComment()
                ];
            }
        }
        return $comments;
    }
}
