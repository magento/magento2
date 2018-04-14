<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\GetProductTypesBySkusInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

/**
 * @inheritdoc
 */
class PlaceReservationsForSalesEvent implements PlaceReservationsForSalesEventInterface
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /*
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        StockResolverInterface $stockResolver,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypesBySkus
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->stockResolver = $stockResolver;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
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

        $skus = array_map(
            function (ItemToSellInterface $item) {
                return $item->getSku();
            },
            $items
        );
        $productTypes = $this->getProductTypesBySkus->execute($skus);
        $this->checkItemsQuantity($items, $productTypes, $stockId);
        $reservations = [];
        /** @var ItemToSellInterface $item */
        foreach ($items as $item) {
            $reservations[] = $this->reservationBuilder
                ->setSku($item->getSku())
                ->setQuantity(-$item->getQuantity())
                ->setStockId($stockId)
                ->setMetadata(sprintf(
                    '%s:%s:%s',
                    $salesEvent->getType(),
                    $salesEvent->getObjectType(),
                    $salesEvent->getObjectId()
                ))
                ->build();
        }
        $this->appendReservations->execute($reservations);
    }

    /**
     * Check is all items salable
     *
     * @return void
     * @throws LocalizedException
     */
    private function checkItemsQuantity(array $items, array $productTypes, int $stockId)
    {
        /** @var ItemToSellInterface $item */
        foreach ($items as $item) {
            if (false === $this->isSourceItemsAllowedForProductType->execute($productTypes[$item->getSku()])) {
                continue;
            }
            $isSalable = $this->isProductSalableForRequestedQty->execute(
                $item->getSku(),
                $stockId,
                $item->getQuantity()
            );
            if (false === $isSalable->isSalable()) {
                $errors = $isSalable->getErrors();
                $errorMessage = array_pop($errors);
                throw new LocalizedException(__($errorMessage->getMessage()));
            }
        }
    }
}
