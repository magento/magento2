<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class ConfigurableOptionsStockStatusFilter implements ConfigurableOptionsFilterInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @param StockConfigurationInterface $stockConfig
     */
    public function __construct(
        StockConfigurationInterface $stockConfig
    ) {
        $this->stockConfig = $stockConfig;
    }

    /**
     * @inheritdoc
     */
    public function filter(ProductInterface $parentProduct, array $childProducts): array
    {
        if ($this->stockConfig->isShowOutOfStock()) {
            $result = $childProducts;
            if ($parentProduct->getIsSalable()) {
                $result = $this->filterInStockProducts($childProducts) ?: $childProducts;
            }
        } else {
            $result = $this->filterInStockProducts($childProducts);
        }

        return $result;
    }

    /**
     * Returns in-stock products
     *
     * @param ProductInterface[] $childProducts
     * @return ProductInterface[]
     */
    private function filterInStockProducts(array $childProducts): array
    {
        $result = [];
        foreach ($childProducts as $childProduct) {
            if ($childProduct->getIsSalable()) {
                $result[] = $childProduct;
            }
        }
        return $result;
    }
}
