<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Catalog\Model\Product;

/**
 * Provide list of bundle selection prices
 */
interface SelectionPriceListProviderInterface
{
    /**
     * @param Product $bundleProduct
     * @param boolean $searchMin
     * @param boolean $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function getPriceList(Product $bundleProduct, $searchMin, $useRegularPrice);
}
