<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Helper\Stock;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Helper\Stock;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;

/**
 * Adapt assignStatusToProduct for multi stocks.
 */
class AdaptAssignStatusToProductPlugin
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param IsProductInStockInterface $isProductInStock
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        IsProductInStockInterface $isProductInStock
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->isProductInStock = $isProductInStock;
    }

    /**
     * @param Stock $subject
     * @param callable $proceed
     * @param Product $product
     * @param int|null $status
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAssignStatusToProduct(
        Stock $subject,
        callable $proceed,
        Product $product,
        $status = null
    ) {
        if (null === $product->getSku()) {
            return;
        }
        if (null === $status) {
            $stockId = $this->getStockIdForCurrentWebsite->execute();
            $status = (int)$this->isProductInStock->execute($product->getSku(), $stockId);
        }
        $proceed($product, $status);
    }
}
