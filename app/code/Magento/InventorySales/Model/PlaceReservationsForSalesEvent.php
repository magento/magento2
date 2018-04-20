<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Api\ReservationBuilderInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;
use Magento\InventoryCatalog\Model\GetProductTypesBySkusInterface;

/**
 * @inheritdoc
 */
class PlaceReservationsForSalesEvent implements PlaceReservationsForSalesEventInterface
{
    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param StockResolverInterface $stockResolver
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     */
    public function __construct(
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        StockResolverInterface $stockResolver,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->stockResolver = $stockResolver;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $items, SalesChannelInterface $salesChannel, SalesEventInterface $salesEvent)
    {
        if (empty($items)) {
            return;
        }

        $stockId = (int)$this->stockResolver->get($salesChannel->getType(), $salesChannel->getCode())->getStockId();

        $skus = [];
        /** @var ItemToSellInterface $item */
        foreach ($items as $item) {
            $skus[] = $item->getSku();
        }
        $productTypes = $this->getProductTypesBySkus->execute($skus);

        $reservations = [];
        /** @var ItemToSellInterface $item */
        foreach ($items as $item) {
            if (true === $this->isSourceItemsAllowedForProductType->execute($productTypes[$item->getSku()])) {
                $reservations[] = $this->reservationBuilder
                    ->setSku($item->getSku())
                    ->setQuantity((float)$item->getQuantity())
                    ->setStockId($stockId)
                    ->setMetadata(sprintf(
                        '%s:%s:%s',
                        $salesEvent->getType(),
                        $salesEvent->getObjectType(),
                        $salesEvent->getObjectId()
                    ))
                    ->build();
            }
        }
        $this->appendReservations->execute($reservations);
    }
}
