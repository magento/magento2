<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

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
     * @var Json
     */
    private $json;

    /**
     * GetOrderWithBrokenReservation constructor.
     * @param GetOrderInFinalState $getOrderInFinalState
     * @param GetReservationsList $getReservationsList
     * @param Json $json
     */
    public function __construct(
        GetOrderInFinalState $getOrderInFinalState,
        GetReservationsList $getReservationsList,
        Json $json
    ) {
        $this->getOrderInFinalState = $getOrderInFinalState;
        $this->getReservationsList = $getReservationsList;
        $this->json = $json;
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
            $metadata = $this->json->unserialize($reservation['metadata']);
            $objectId = $metadata['object_id'];
            if(!array_key_exists($objectId, $result)) {
                $result[$objectId] = .0;
            }
            $result[$objectId] += (float)$reservation['quantity'];
        }
        $result = array_filter($result);
        if(count($result) === 0){
            return [];
        }

        /** @var Collection $orders */
        $orders = $this->getOrderInFinalState->execute(array_keys($result));
        return $orders->getItems();
    }
}
