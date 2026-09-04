<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel\Product\Checker;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

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
            return $product->isSalable() || $this->stockConfiguration->isShowOutOfStock();
        }

        return false;
    }
}
