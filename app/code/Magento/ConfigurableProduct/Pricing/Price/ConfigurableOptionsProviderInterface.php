<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provide configurable sub-products for price calculation
 * @api
 * @since 100.1.1
 */
interface ConfigurableOptionsProviderInterface
{
    /**
     * @param ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     * @since 100.1.1
     */
    public function getProducts(\Magento\Catalog\Api\Data\ProductInterface $product);
}
