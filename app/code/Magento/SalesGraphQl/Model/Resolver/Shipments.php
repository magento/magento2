<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order;

/**
 * Resolve shipment information for order
 */
class Shipments implements ResolverInterface
{
    /**
     * @param TimezoneInterface|null $timezone
     */
    public function __construct(
        private ?TimezoneInterface $timezone = null
    ) {
        $this->timezone = $timezone ?: ObjectManager::getInstance()->get(TimezoneInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
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
                    'timestamp' => $this->timezone->date($comment->getCreatedAt())
                        ->format(DateTime::DATETIME_PHP_FORMAT),
                    'message' => $comment->getComment()
                ];
            }
        }
        return $comments;
    }
}
