<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class GetOrderWithBrokenReservation
{
    /**
     * @var GetOrderInFinalState
     */
    private $getOrderInFinalState;

    /**
     * @var GetReservationsList
     */
    private $getReservationsList;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * @param GetOrderInFinalState $getOrderInFinalState
     * @param GetReservationsList $getReservationsList
     * @param SerializerInterface $serialize
     */
    public function __construct(
        GetOrderInFinalState $getOrderInFinalState,
        GetReservationsList $getReservationsList,
        SerializerInterface $serialize
    ) {
        $this->getOrderInFinalState = $getOrderInFinalState;
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
        if(empty($result)){
            return [];
        }

        /** @var OrderInterface[] $orders */
        return $this->getOrderInFinalState->execute(array_keys($result));
    }
}
