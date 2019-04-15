<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistencyFactory;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Collects all existing and missing reservations in order to calculate inconsistency
 */
class Collector
{
    /**
     * @var SaleableQuantityInconsistency[]
     */
    private $items = [];

    /**
     * @var \Magento\InventoryReservationCli\Model\SaleableQuantityInconsistencyFactory
     */
    private $saleableQuantityInconsistencyFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @param SaleableQuantityInconsistencyFactory $saleableQuantityInconsistencyFactory
     * @param SerializerInterface $serializer
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     */
    public function __construct(
        SaleableQuantityInconsistencyFactory $saleableQuantityInconsistencyFactory,
        SerializerInterface $serializer,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
    ) {
        $this->saleableQuantityInconsistencyFactory = $saleableQuantityInconsistencyFactory;
        $this->serializer = $serializer;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
    }

    /**
     * @param ReservationInterface $reservation
     */
    public function addReservation(ReservationInterface $reservation): void
    {
        $metadata = $this->serializer->unserialize($reservation->getMetadata());
        $objectId = $metadata['object_id'];
        $stockId = $reservation->getStockId();
        $key = $objectId . '-' . $stockId;

        if (!isset($this->items[$key])) {
            $this->items[$key] = $this->saleableQuantityInconsistencyFactory->create();
        }

        $this->items[$key]->setObjectId((int)$objectId);
        $this->items[$key]->setStockId((int)$stockId);
        $this->items[$key]->addItemQty($reservation->getSku(), $reservation->getQuantity());
    }

    /**
     * @param OrderInterface $order
     */
    public function addOrder(OrderInterface $order): void
    {
        $objectId = $order->getEntityId();
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
        $key = $objectId . '-' . $stockId;

        if (!isset($this->items[$key])) {
            $this->items[$key] = $this->saleableQuantityInconsistencyFactory->create();
        }

        $this->items[$key]->setOrder($order);
    }

    /**
     * @return SaleableQuantityInconsistency[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param SaleableQuantityInconsistency[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
