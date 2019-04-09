<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class GetOrderWithBrokenReservation
{
    /**
     * @var GetReservationsList
     */
    private $getReservationsList;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * @param GetReservationsList $getReservationsList
     * @param SerializerInterface $serialize
     */
    public function __construct(
        GetReservationsList $getReservationsList,
        SerializerInterface $serialize
    ) {
        $this->getReservationsList = $getReservationsList;
        $this->serialize = $serialize;
    }

    /**
     * @return OrderInterface[]
     */
    public function execute(): array
    {
        /** @var array $orderListReservations */
        $allReservations = $this->getReservationsList->getListReservationsTotOrder();

        /** @var array $result */
        $result = [];
        foreach ($allReservations as $reservation){
            /** @var array $metadata */
            $metadata = $this->serialize->unserialize($reservation['metadata']);
            $objectId = $metadata['object_id'];
            if(!array_key_exists($objectId, $result)) {
                $result[$objectId] = (float) 0;
            }
            $result[$objectId] += (float)$reservation['quantity'];
        }
        $result = array_filter($result);
        return $result;
    }
}
