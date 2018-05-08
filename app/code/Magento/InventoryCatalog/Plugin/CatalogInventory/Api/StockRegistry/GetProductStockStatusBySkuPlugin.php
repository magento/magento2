<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalog\Model\GetProductStockStatusForCurrentWebsiteBySku;

/**
 * Retrieve stock status for given product sku.
 */
class GetProductStockStatusBySkuPlugin
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
     * Retrieve stock status for given product sku.
     *
     * @param StockRegistryInterface $subject
     * @param callable $proceed
     * @param string $productSku
     * @param null $scopeId
     * @return int
     * @throws \Magento\Framework\Exception\InputException in case requested product doesn't exist.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetProductStockStatusBySku(
        StockRegistryInterface $subject,
        callable $proceed,
        $productSku,
        $scopeId = null
    ): int {
        return (int)$this->getProductStockStatusForCurrentWebsiteBySku->execute($productSku)->getStockStatus();
    }
}
