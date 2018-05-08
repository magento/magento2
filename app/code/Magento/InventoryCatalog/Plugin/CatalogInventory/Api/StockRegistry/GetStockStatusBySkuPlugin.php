<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalog\Model\GetProductStockStatusForCurrentWebsiteBySku;

/**
 * Retrieve stock status configuration for given product sku.
 */
class GetStockStatusBySkuPlugin
{
    /**
     * @var GetProductStockStatusForCurrentWebsiteBySku
     */
    private $getProductStockStatusForCurrentWebsiteBySku;

    /**
     * @param GetProductStockStatusForCurrentWebsiteBySku $getProductStockStatusForCurrentWebsiteBySku
     */
    public function __construct(
        GetProductStockStatusForCurrentWebsiteBySku $getProductStockStatusForCurrentWebsiteBySku
    ) {
        $this->getProductStockStatusForCurrentWebsiteBySku = $getProductStockStatusForCurrentWebsiteBySku;
    }

    /**
     * Retrieve stock status configuration for given product sku.
     *
     * @param StockRegistryInterface $subject
     * @param callable $proceed
     * @param string $productSku
     * @param int $scopeId
     * @return StockStatusInterface
     * @throws \Magento\Framework\Exception\InputException in case requested product doesn't exist.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetStockStatusBySku(
        StockRegistryInterface $subject,
        callable $proceed,
        $productSku,
        $scopeId = null
    ): StockStatusInterface {
        return $this->getProductStockStatusForCurrentWebsiteBySku->execute($productSku);
    }
}
