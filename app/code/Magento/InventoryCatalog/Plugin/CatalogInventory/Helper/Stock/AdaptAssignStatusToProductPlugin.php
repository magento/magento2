<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Helper\Stock;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Helper\Stock;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
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
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param IsProductSalableInterface $isProductSalable
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        IsProductSalableInterface $isProductSalable,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->isProductSalable = $isProductSalable;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param Stock $subject
     * @param callable $proceed
     * @param Product $product
     * @param int|null $status
     * @return void
     *
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

        $stockId = $this->getStockIdForCurrentWebsite->execute();

        if ($this->defaultStockProvider->getId() !== $stockId && null === $status) {
            $status = (int)$this->isProductSalable->execute($product->getSku(), $stockId);
        }
        $proceed($product, $status);
    }
}
