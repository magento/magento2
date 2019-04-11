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
 * Get list of reservations for Order entity.
 */
class GetOrdersWithNotCompensatedReservations
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
     * Get list of reservations for Order entity.
     *
     * @return array
     */
    public function execute(): array
    {
        /** @var array $reservationList */
        $reservationList = $this->getReservationsList->execute();

        /** @var array $result */
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
}
