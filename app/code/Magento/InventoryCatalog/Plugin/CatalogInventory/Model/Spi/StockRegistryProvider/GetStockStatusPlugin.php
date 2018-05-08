<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\Spi\StockRegistryProvider;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\InventoryCatalog\Model\GetProductStockStatusForCurrentWebsiteBySku;
use Magento\InventoryCatalog\Model\GetSkusByProductIds;

/**
 * Retrieve stock status configuration for given product id.
 */
class GetStockStatusPlugin
{
    /**
     * @var GetSkusByProductIds
     */
    private $getSkusByProductIds;

    /**
     * @var GetProductStockStatusForCurrentWebsiteBySku
     */
    private $getProductStockStatusForCurrentWebsiteBySku;

    /**
     * @param GetSkusByProductIds $getSkusByProductIds
     * @param GetProductStockStatusForCurrentWebsiteBySku $getProductStockStatusForCurrentWebsiteBySku
     */
    public function __construct(
        GetSkusByProductIds $getSkusByProductIds,
        GetProductStockStatusForCurrentWebsiteBySku $getProductStockStatusForCurrentWebsiteBySku
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getProductStockStatusForCurrentWebsiteBySku = $getProductStockStatusForCurrentWebsiteBySku;
    }

    /**
     * Retrieve stock status configuration for given product id.
     *
     * @param StockRegistryProviderInterface $subject
     * @param callable $proceed
     * @param int $productId
     * @param int $scopeId
     * @return StockStatusInterface
     * @throws \Magento\Framework\Exception\InputException in case requested product doesn't exist.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetStockStatus(
        StockRegistryProviderInterface $subject,
        callable $proceed,
        $productId,
        $scopeId = null
    ): StockStatusInterface {
        $skus = $this->getSkusByProductIds->execute([$productId]);
        $sku = reset($skus);

        return $this->getProductStockStatusForCurrentWebsiteBySku->execute($sku);
    }
}
