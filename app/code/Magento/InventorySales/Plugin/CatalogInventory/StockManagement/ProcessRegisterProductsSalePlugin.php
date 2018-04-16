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
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryCatalog\Model\GetProductTypesBySkusInterface;
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
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var CheckItemsQuantity
     */
    private $checkItemsQuantity;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StockResolverInterface $stockResolver
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param CheckItemsQuantity $checkItemsQuantity
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        WebsiteRepositoryInterface $websiteRepository,
        StockResolverInterface $stockResolver,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        CheckItemsQuantity $checkItemsQuantity
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->websiteRepository = $websiteRepository;
        $this->stockResolver = $stockResolver;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->checkItemsQuantity = $checkItemsQuantity;
    }

    /**
     * @param RegisterProductSaleInterface $subject
     * @param callable $proceed
     * @param float[] $items
     * @param int|null $websiteId
     *
     * @return array
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
        $this->checkItemsQuantity->execute($itemsBySku, $productTypes, $stockId);
        return [];
    }
}
