<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryReservationCli\Model\ResourceModel\GetReservationsList;
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;

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
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @param GetReservationsList $getReservationsList
     * @param SerializerInterface $serializer
     * @param ReservationBuilderInterface $reservationBuilder
     */
    public function __construct(
        GetReservationsList $getReservationsList,
        SerializerInterface $serializer,
        ReservationBuilderInterface $reservationBuilder
    ) {
        $this->getReservationsList = $getReservationsList;
        $this->serializer = $serializer;
        $this->reservationBuilder = $reservationBuilder;
    }

    /**
     * Add existing reservations
     * @param Collector $collector
     * @throws ValidationException
     */
    public function execute(Collector $collector): void
    {
        $reservationList = $this->getReservationsList->execute();
        foreach ($reservationList as $reservation) {
            /** @var array $metadata */
            $metadata = $this->serializer->unserialize($reservation['metadata']);
            $orderType = $metadata['object_type'];

            if ($orderType !== 'order') {
                continue;
            }

            $reservation = $this->reservationBuilder
                ->setMetadata($reservation['metadata'])
                ->setStockId((int)$reservation['stock_id'])
                ->setSku($reservation['sku'])
                ->setQuantity((float)$reservation['quantity'])
                ->build();

            $collector->addReservation($reservation);
        }
    }
}
