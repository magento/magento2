<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provide configurable sub-products for price calculation
 * @api
 * @since 2.2.0
 */
interface ConfigurableOptionsProviderInterface
{
    /**
     * @param ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     * @since 2.2.0
     */
    public function getProducts(\Magento\Catalog\Api\Data\ProductInterface $product);
}
