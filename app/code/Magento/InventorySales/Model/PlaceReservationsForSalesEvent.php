<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;

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
     * @var GetStockBySalesChannelInterface
     */
    private $getStockBySalesChannel;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SalesEventToArrayConverter
     */
    private $salesEventToArrayConverter;

    /**
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param GetStockBySalesChannelInterface $getStockBySalesChannel
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param SerializerInterface $serializer
     * @param SalesEventToArrayConverter $salesEventToArrayConverter
     */
    public function __construct(
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        GetStockBySalesChannelInterface $getStockBySalesChannel,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        SerializerInterface $serializer,
        SalesEventToArrayConverter $salesEventToArrayConverter
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->serializer = $serializer;
        $this->salesEventToArrayConverter = $salesEventToArrayConverter;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $items, SalesChannelInterface $salesChannel, SalesEventInterface $salesEvent): void
    {
        if (empty($items)) {
            return;
        }

        $stockId = $this->getStockBySalesChannel->execute($salesChannel)->getStockId();

        $skus = [];
        /** @var ItemToSellInterface $item */
        foreach ($items as $item) {
            $skus[] = $item->getSku();
        }
        $productTypes = $this->getProductTypesBySkus->execute($skus);

        $reservations = [];
        /** @var ItemToSellInterface $item */
        foreach ($items as $item) {
            $currentSku = $item->getSku();
            $skuNotExistInCatalog = !isset($productTypes[$currentSku]);
            if ($skuNotExistInCatalog ||
                $this->isSourceItemManagementAllowedForProductType->execute($productTypes[$currentSku])) {
                $reservations[] = $this->reservationBuilder
                    ->setSku($item->getSku())
                    ->setQuantity((float)$item->getQuantity())
                    ->setStockId($stockId)
                    ->setMetadata($this->serializer->serialize($this->salesEventToArrayConverter->execute($salesEvent)))
                    ->build();
            }
        }
        $this->appendReservations->execute($reservations);
    }
}
