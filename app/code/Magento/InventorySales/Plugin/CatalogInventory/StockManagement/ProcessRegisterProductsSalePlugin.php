<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Api\RegisterProductSaleInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryCatalog\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;

/**
 * Class provides around Plugin on RegisterProductSaleInterface::registerProductsSale
 */
class ProcessRegisterProductsSalePlugin
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /*
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        WebsiteRepositoryInterface $websiteRepository,
        StockResolverInterface $stockResolver,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->websiteRepository = $websiteRepository;
        $this->stockResolver = $stockResolver;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
    }

    /**
     * @param RegisterProductSaleInterface $subject
     * @param callable $proceed
     * @param float[] $items
     * @param int|null $websiteId
     *
     * @return []
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRegisterProductsSale(
        RegisterProductSaleInterface $subject,
        callable $proceed,
        $items,
        $websiteId = null
    ) {
        if (empty($items)) {
            return [];
        }
        if (null === $websiteId) {
            throw new LocalizedException(__('$websiteId parameter is required'));
        }
        $productSkus = $this->getSkusByProductIds->execute(array_keys($items));
        $itemsBySku = [];
        foreach ($productSkus as $productId => $sku) {
            $itemsBySku[$sku] = $items[$productId];
        }
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $stockId = (int)$this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        $productTypes = $this->getProductTypesBySkus->execute(array_keys($itemsBySku));
        $this->checkItemsQuantity($itemsBySku, $productTypes, $stockId);
        return [];
    }

    /**
     * Check whether all items salable
     *
     * @return void
     * @throws LocalizedException
     */
    private function checkItemsQuantity(array $items, array $productTypes, int $stockId)
    {
        foreach ($items as $sku => $qty) {
            if (false === $this->isSourceItemsAllowedForProductType->execute($productTypes[$sku])) {
                continue;
            }
            /** @var ProductSalableResultInterface $isSalable */
            $isSalable = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $qty);
            if (false === $isSalable->isSalable()) {
                $errors = $isSalable->getErrors();
                /** @var ProductSalabilityErrorInterface $errorMessage */
                $errorMessage = array_pop($errors);
                throw new LocalizedException(__($errorMessage->getMessage()));
            }
        }
    }
}
