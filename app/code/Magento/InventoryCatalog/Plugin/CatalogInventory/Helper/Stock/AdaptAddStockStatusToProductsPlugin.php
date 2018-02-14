<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Helper\Stock;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;

/**
 * Adapt addStockStatusToProducts for multi stocks.
 */
class AdaptAddStockStatusToProductsPlugin
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param IsProductSalableInterface $isProductSalable
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        IsProductSalableInterface $isProductSalable
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->isProductSalable = $isProductSalable;
    }

    /**
     * @param Stock $subject
     * @param callable $proceed
     * @param AbstractCollection $productCollection
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockStatusToProducts(
        Stock $subject,
        callable $proceed,
        AbstractCollection $productCollection
    ) {
        $stockId = $this->getStockIdForCurrentWebsite->execute();

        /** @var Product $product */
        foreach ($productCollection as $product) {
            $isSalable = (int)$this->isProductSalable->execute($product->getSku(), $stockId);
            $product->setIsSalable($isSalable);
        }
    }
}
