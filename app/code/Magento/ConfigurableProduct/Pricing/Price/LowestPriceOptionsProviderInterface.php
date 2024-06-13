<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Retrieve list of products where each product contains lower price than others at least for one possible price type
 * @api
 * @since 100.1.3
 */
interface LowestPriceOptionsProviderInterface
{
    /**
     * @param ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     * @since 100.1.3
     */
    public function getProducts(\Magento\Catalog\Api\Data\ProductInterface $product);
}
