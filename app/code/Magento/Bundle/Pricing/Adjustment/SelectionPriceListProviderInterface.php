<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Catalog\Model\Product;

/**
 * Provide list of bundle selection prices
 * @api
 * @since 100.2.0
 */
interface SelectionPriceListProviderInterface
{
    /**
     * @param Product $bundleProduct
     * @param boolean $searchMin
     * @param boolean $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     * @since 100.2.0
     */
    public function getPriceList(Product $bundleProduct, $searchMin, $useRegularPrice);
}
