<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryReservationCli\Model\ResourceModel\GetReservationsList;

/**
 * Add existing reservations
 */
class AddExistingReservations
{
    /**
     * @var GetReservationsList
     */
    private $getReservationsList;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param GetReservationsList $getReservationsList
     * @param SerializerInterface $serializer
     */
    public function __construct(
        GetReservationsList $getReservationsList,
        SerializerInterface $serializer
    ) {
        $this->getReservationsList = $getReservationsList;
        $this->serializer = $serializer;
    }

    /**
     * Add existing reservations
     * @param Collector $collector
     */
    public function execute(Collector $collector): void
    {
        $reservationList = $this->getReservationsList->execute();
        foreach ($reservationList as $reservation) {
            /** @var array $metadata */
            $metadata = $this->serializer->unserialize($reservation['metadata']);
            $objectId = (int)$metadata['object_id'];
            $sku = $reservation['sku'];
            $quantity = (float)$reservation['quantity'];
            $orderType = $metadata['object_type'];

            if ($orderType !== 'order') {
                continue;
            }

            $collector->add($objectId, $sku, $quantity);
        }
    }
}
