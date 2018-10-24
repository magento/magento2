<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Api\RegisterProductSaleInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySales\Model\CheckItemsQuantity;

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
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var CheckItemsQuantity
     */
    private $checkItemsQuantity;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StockResolverInterface $stockResolver
     * @param CheckItemsQuantity $checkItemsQuantity
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        WebsiteRepositoryInterface $websiteRepository,
        StockResolverInterface $stockResolver,
        CheckItemsQuantity $checkItemsQuantity,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->websiteRepository = $websiteRepository;
        $this->stockResolver = $stockResolver;
        $this->checkItemsQuantity = $checkItemsQuantity;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
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
        $productTypes = $this->getProductTypesBySkus->execute($productSkus);

        $itemsBySku = [];
        foreach ($productSkus as $productId => $sku) {
            if (false === $this->isSourceItemManagementAllowedForProductType->execute($productTypes[$sku])) {
                continue;
            }
            $itemsBySku[$sku] = $items[$productId];
        }

        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $stockId = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        $this->checkItemsQuantity->execute($itemsBySku, $stockId);
        return [];
    }
}
