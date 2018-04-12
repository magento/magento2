<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\RegisterSalesEventInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

/**
 * @inheritdoc
 */
class RegisterSalesEvent implements RegisterSalesEventInterface
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
     * @var StockResolverInterface
     */
    private $stockResolver;

    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        StockResolverInterface $stockResolver
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $items, SalesChannelInterface $salesChannel, SalesEventInterface $salesEvent)
    {
        if (empty($items)) {
            return;
        }

        if (null === $salesChannel) {
            throw new LocalizedException(__('$salesChannel parameter is required'));
        }

        // TODO typecast needed because StockInterface::getStockId() is supposed to return int but actually doesn't
        $stockId = (int)$this->stockResolver->get($salesChannel->getType(), $salesChannel->getCode())->getStockId();

        $this->checkItemsQuantity($items, $stockId);
        $reservations = [];
        foreach ($items as $sku => $qty) {
            $reservations[] = $this->reservationBuilder
                ->setSku($sku)
                ->setQuantity(-(float) $qty)
                ->setStockId($stockId)
                ->setMetadata(sprintf('%s:%d', $salesEvent->getType(), $salesEvent->getObjectId()))
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
    private function checkItemsQuantity(array $items, int $stockId)
    {
        foreach ($items as $sku => $qty) {
            $isSalable = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $qty)->isSalable();
            if (!$isSalable) {
                throw new LocalizedException(
                    __('Not all of your products are available in the requested quantity.')
                );
            }
        }
    }
}
