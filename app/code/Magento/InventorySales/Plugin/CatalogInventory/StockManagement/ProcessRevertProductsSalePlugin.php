<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;

/**
 * Class provides around Plugin on \Magento\CatalogInventory\Model\StockManagement::revertProductsSale
 */
class ProcessRevertProductsSalePlugin
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
     * @param float[] $items
     * @param int|null $websiteId
     * @return StockItemInterface[]
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRevertProductsSale(StockManagement $subject, callable $proceed, $items, $websiteId = null)
    {
        if (empty($items)) {
            return [];
        }
        if (null === $websiteId) {
            //TODO: Do we need to throw exception?
            throw new LocalizedException(__('$websiteId parameter is required'));
        }
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
        $productSkus = $this->getSkusByProductIds->execute(array_keys($items));

        $reservations = [];
        foreach ($productSkus as $productId => $sku) {
            $reservations[] = $this->reservationBuilder
                ->setSku($sku)
                ->setQuantity((float)$items[$productId])
                ->setStockId($stockId)
                ->build();
        }
        $this->appendReservations->execute($reservations);

        return [];
    }
}
