<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel\Product\Checker;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Check is available add to compare.
 */
class AddToCompareAvailability implements ArgumentInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(StockConfigurationInterface $stockConfiguration)
    {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Is product available for comparison.
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function isAvailableForCompare(ProductInterface $product): bool
    {
        if ((int)$product->getStatus() !== Status::STATUS_DISABLED) {
            return $this->isInStock($product) || $this->stockConfiguration->isShowOutOfStock();
        }

        return false;
    }

    /**
     * Get is in stock status.
     *
     * @param ProductInterface $product
     * @return bool
     */
    private function isInStock(ProductInterface $product): bool
    {
        $quantityAndStockStatus = $product->getQuantityAndStockStatus();
        if (!$quantityAndStockStatus) {
            return $product->isSalable();
        }

        return $quantityAndStockStatus['is_in_stock'] ?? false;
    }
}
