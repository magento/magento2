<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventoryCatalog\Model\GetProductTypesBySkusInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Api\ReservationBuilderInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;

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
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * @var CheckItemsQuantity
     */
    private $checkItemsQuantity;

    /**
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param StockResolverInterface $stockResolver
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param CheckItemsQuantity $checkItemsQuantity
     */
    public function __construct(
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        StockResolverInterface $stockResolver,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        CheckItemsQuantity $checkItemsQuantity
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->stockResolver = $stockResolver;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->checkItemsQuantity = $checkItemsQuantity;
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
        $itemsBySku = [];
        /** @var ItemToSellInterface $item */
        foreach ($items as $item) {
            //TODO: Failed for bundle products.. need to fix it
            $itemsBySku[$item->getSku()] = $item->getQuantity();
        }
        $skus = array_keys($itemsBySku);
        $productTypes = $this->getProductTypesBySkus->execute($skus);
        $this->checkItemsQuantity->execute($itemsBySku, $productTypes, $stockId);
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
