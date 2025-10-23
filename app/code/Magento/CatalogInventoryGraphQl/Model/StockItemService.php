<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\Stock\Item;

/**
 * Service to provide stock item for given product
 */
class StockItemService
{
    /**
     * Configurable product type code
     */
    private const PRODUCT_TYPE_CONFIGURABLE = "configurable";

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepositoryInterface;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param StockRegistry $stockRegistry
     */
    public function __construct(
        ProductRepositoryInterface $productRepositoryInterface,
        StockRegistry $stockRegistry
    ) {
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Returns stock item if the product is available
     *
     * @param Product|null $product
     * @return Item|null
     * @throws LocalizedException
     */
    public function getStockItem(?Product $product): ?Item
    {
        if (!isset($product)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        if ($product->getTypeId() === self::PRODUCT_TYPE_CONFIGURABLE) {
            $product = $this->productRepositoryInterface->get($product->getSku());
        }
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
    }
}
