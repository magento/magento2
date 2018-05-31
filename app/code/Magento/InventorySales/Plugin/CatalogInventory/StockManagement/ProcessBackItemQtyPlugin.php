<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Model\StockManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Framework\Exception\InputException;

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
     * @var StockByWebsiteIdResolverInterface
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
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypesBySkus
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    /**
     * @param StockManagement $subject
     * @param callable $proceed
     * @param int $productId
     * @param float $qty
     * @param int|null $scopeId
     * @return bool
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBackItemQty(
        StockManagement $subject,
        callable $proceed,
        $productId,
        $qty,
        $scopeId = null
    ): bool {
        if (null === $scopeId) {
            throw new LocalizedException(__('$scopeId is required'));
        }

        // As it was decided that the inventory doesn’t depend on the catalog (these two system are independent)
        // it is necessary for us to have an ability to process orders even with deleted or non-existing products
        try {
            $productSku = $this->getSkusByProductIds->execute([$productId])[$productId];
        } catch (InputException $e) {
            return true;
        }
        $productType = $this->getProductTypesBySkus->execute([$productSku])[$productSku];

        if (true === $this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$scopeId)->getStockId();
            $reservation = $this->reservationBuilder
                ->setSku($productSku)
                ->setQuantity((float)$qty)
                ->setStockId($stockId)
                ->build();
            $this->appendReservations->execute([$reservation]);
        }

        return true;
    }
}
