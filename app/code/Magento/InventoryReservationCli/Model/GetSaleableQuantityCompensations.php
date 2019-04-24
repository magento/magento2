<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

/**
 * Returns compensation reservations for given inconsistencies
 */
class GetSaleableQuantityCompensations
{
    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @param ReservationBuilderInterface $reservationBuilder
     * @param SerializerInterface $serializer
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     */
    public function __construct(
        ReservationBuilderInterface $reservationBuilder,
        SerializerInterface $serializer,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->serializer = $serializer;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
    }

    /**
     * Returns compensation reservations for given inconsistencies
     *
     * @param SaleableQuantityInconsistency[] $inconsistencies
     * @return ReservationInterface[]
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $inconsistencies): array
    {
        $compensations = [];
        foreach ($inconsistencies as $inconsistency) {
            foreach ($inconsistency->getItems() as $sku => $quantity) {
                $compensations[] = $this->reservationBuilder
                    ->setSku($sku)
                    ->setQuantity((float)$quantity * -1)
                    ->setStockId($inconsistency->getStockId())
                    ->setMetadata($this->serializer->serialize([
                        'event_type' => 'manual_compensation',
                        'object_type' => 'order',
                        'object_id' => $inconsistency->getObjectId(),
                    ]))
                    ->build();
            }
        }

        return $compensations;
    }
}
