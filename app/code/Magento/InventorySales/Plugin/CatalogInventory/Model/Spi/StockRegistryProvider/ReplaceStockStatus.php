<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\Model\Spi\StockRegistryProvider;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\InventoryCatalog\Model\GetSkusByProductIds;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Replace stock status configuration for given product id.
 */
class ReplaceStockStatus
{
    /**
     * @var StockRegistryStorage
     */
    private $registryStorage;

    /**
     * @var StockStatusInterfaceFactory
     */
    private $stockStatusFactory;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var GetSkusByProductIds
     */
    private $getSkusByProductIds;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @param StockRegistryStorage $registryStorage
     * @param StockStatusInterfaceFactory $stockStatusFactory
     * @param IsProductSalableInterface $isProductSalable
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param GetSkusByProductIds $getSkusByProductIds
     * @param GetProductSalableQtyInterface $getProductSalableQty
     */
    public function __construct(
        StockRegistryStorage $registryStorage,
        StockStatusInterfaceFactory $stockStatusFactory,
        IsProductSalableInterface $isProductSalable,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        GetSkusByProductIds $getSkusByProductIds,
        GetProductSalableQtyInterface $getProductSalableQty
    ) {
        $this->registryStorage = $registryStorage;
        $this->stockStatusFactory = $stockStatusFactory;
        $this->isProductSalable = $isProductSalable;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getProductSalableQty = $getProductSalableQty;
    }

    /**
     * Replace stock status configuration for given product and website ids.
     *
     * @param StockRegistryProviderInterface $subject
     * @param callable $proceed
     * @param int $productId
     * @param int $scopeId
     * @return StockStatusInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\InputException in case requested product doesn't exist.
     */
    public function aroundGetStockStatus(
        StockRegistryProviderInterface $subject,
        callable $proceed,
        $productId,
        $scopeId
    ): StockStatusInterface {
        $stockStatus = $this->registryStorage->getStockStatus($productId, $scopeId);
        if (null === $stockStatus) {
            $stockStatus = $this->stockStatusFactory->create();
            $stockId = $this->getStockIdForCurrentWebsite->execute();
            $skus = $this->getSkusByProductIds->execute([$productId]);
            $sku = reset($skus);
            $status = (int)$this->isProductSalable->execute($sku, $stockId);
            $qty = $this->getProductSalableQty->execute($sku, $stockId);

            $stockStatus->setProductId($productId);
            $stockStatus->setStockId($stockId);
            $stockStatus->setStockStatus($status);
            $stockStatus->setQty($qty);

            $this->registryStorage->setStockStatus($productId, $scopeId, $stockStatus);
        }

        return $stockStatus;
    }
}
