<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Retrieve list of products where each product contains lower price than others at least for one possible price type
 */
interface LowestPriceOptionsProviderInterface
{
    /**
     * @param ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProducts(\Magento\Catalog\Api\Data\ProductInterface $product);
}
