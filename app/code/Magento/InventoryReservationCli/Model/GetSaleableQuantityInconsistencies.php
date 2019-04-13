<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryReservationCli\Model\ResourceModel\GetReservationsList;

/**
 * Filter orders for missing initial reservation
 */
class GetSaleableQuantityInconsistencies
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
     * @var GetOrdersInNotFinalState
     */
    private $getOrdersInNotFinalState;

    /**
     * @param GetReservationsList $getReservationsList
     * @param SerializerInterface $serialize
     * @param GetOrdersInNotFinalState $getOrdersInNotFinalState
     */
    public function __construct(
        GetReservationsList $getReservationsList,
        SerializerInterface $serialize,
        GetOrdersInNotFinalState $getOrdersInNotFinalState
    ) {
        $this->getReservationsList = $getReservationsList;
        $this->serialize = $serialize;
        $this->getOrdersInNotFinalState = $getOrdersInNotFinalState;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $reservationList = $this->getReservationsList->execute();
        $expectedReservations = $this->getExpectedReservations();

        $result = [];
        foreach ($reservationList as $reservation) {
            /** @var array $metadata */
            $metadata = $this->serialize->unserialize($reservation['metadata']);
            $objectId = $metadata['object_id'];
            $sku = $reservation['sku'];
            $orderType = $metadata['object_type'];

            if ($orderType !== 'order') {
                continue;
            }

            if (!isset($result[$objectId])) {
                $result[$objectId] = [];
            }
            if (!isset($result[$objectId][$sku])) {
                $result[$objectId][$sku] = 0.0;
            }

            $result[$objectId][$sku] += (float) $reservation['quantity'];
        }

        foreach ($result as &$entry) {
            $entry = array_filter($entry);
        }

        return array_filter($result);
    }

    private function getExpectedReservations()
    {
        $incompleteOrders = $this->getOrdersInNotFinalState->execute();

        $result = [];
        foreach ($incompleteOrders as $order) {
            $entityId = $order->getEntityId();
            $list[$entityId] = [
                'increment_id' => $order->getIncrementId(),
                'skus' => []
            ];
            foreach ($order->getItems() as $item) {
                $list[$entityId]['skus'][$item->getSku()] = (float)$item->getQtyOrdered();
            }
        }

        return $list;
    }
}
