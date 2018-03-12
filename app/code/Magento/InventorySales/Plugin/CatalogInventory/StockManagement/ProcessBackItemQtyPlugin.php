<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Model\StockManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;

/**
 * Class provides around Plugin on \Magento\CatalogInventory\Model\StockManagement::backItemQty
 */
class ProcessBackItemQtyPlugin
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
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
    }

    /**
     * @param StockManagement $subject
     * @param callable $proceed
     * @param int $productId
     * @param float $qty
     * @param int|null $scopeId
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBackItemQty(StockManagement $subject, callable $proceed, $productId, $qty, $scopeId = null)
    {
        if (null === $scopeId) {
            //TODO: is we need to throw exception?
            throw new LocalizedException(__('$scopeId is required'));
        }
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$scopeId)->getStockId();
        $productSku = $this->getSkusByProductIds->execute([$productId])[$productId];

        $reservation = $this->reservationBuilder
            ->setSku($productSku)
            ->setQuantity((float)$qty)
            ->setStockId($stockId)
            ->build();
        $this->appendReservations->execute([$reservation]);

        return true;
    }
}
