<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provide configurable sub-products for price calculation
 */
interface ConfigurableOptionsProviderInterface
{
    /**
     * @param ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProducts(\Magento\Catalog\Api\Data\ProductInterface $product);
}
