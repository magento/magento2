<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Filter configurable child products for price calculation
 */
interface ConfigurableOptionsFilterInterface
{
    /**
     * Filter configurable child products for price calculation
     *
     * @param ProductInterface $parentProduct
     * @param ProductInterface[] $childProducts
     * @return array
     */
    public function filter(ProductInterface $parentProduct, array $childProducts): array;
}
