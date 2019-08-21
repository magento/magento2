<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryReservationCli\Model\GetOrdersInNotFinalState;
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

/**
 * Add expected reservations by current incomplete orders
 */
class AddExpectedReservations
{
    /**
     * @var GetOrdersInNotFinalState
     */
    private $getOrdersInNotFinalState;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param GetOrdersInNotFinalState $getOrdersInNotFinalState
     * @param ReservationBuilderInterface $reservationBuilder
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param SerializerInterface $serializer
     */
    public function __construct(
        GetOrdersInNotFinalState $getOrdersInNotFinalState,
        ReservationBuilderInterface $reservationBuilder,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        SerializerInterface $serializer
    ) {
        $this->getOrdersInNotFinalState = $getOrdersInNotFinalState;
        $this->reservationBuilder = $reservationBuilder;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->serializer = $serializer;
    }

    /**
     * Add expected reservations by current incomplete orders.
     *
     * @param Collector $collector
     * @throws ValidationException
     */
    public function execute(Collector $collector): void
    {
        foreach ($this->getOrdersInNotFinalState->execute() as $order) {
            $websiteId = (int)$order->getStore()->getWebsiteId();
            $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();

            foreach ($order->getItems() as $item) {
                if ($item->getHasChildren()) {
                    continue;
                }
                $reservation = $this->reservationBuilder
                    ->setSku($item->getSku())
                    ->setQuantity((float)$item->getQtyOrdered())
                    ->setStockId($stockId)
                    ->setMetadata($this->serializer->serialize(['object_id' => (int)$order->getEntityId()]))
                    ->build();

                $collector->addReservation($reservation);
                $collector->addOrder($order);
            }
        }
    }
}
