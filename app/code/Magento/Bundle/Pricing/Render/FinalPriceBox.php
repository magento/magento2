<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Render;

use Magento\Bundle\Pricing\Price;
use Magento\Catalog\Pricing\Render as CatalogRender;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;

/**
 * Class for final_price rendering
 */
class FinalPriceBox extends CatalogRender\FinalPriceBox
{
    /**
     * Check if bundle product has one or more options, or custom options, with different prices
     *
     * @return bool
     */
    public function showRangePrice()
    {
        //Check the bundle options
        /** @var Price\BundleOptionPrice $bundleOptionPrice */
        $bundleOptionPrice = $this->getPriceType(Price\BundleOptionPrice::PRICE_CODE);
        $showRange = $bundleOptionPrice->getValue() != $bundleOptionPrice->getMaxValue();

        if (!$showRange) {
            //Check the custom options, if any
            /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
            $customOptionPrice = $this->getPriceType(CustomOptionPrice::PRICE_CODE);
            $showRange =
                $customOptionPrice->getCustomOptionRange(true) != $customOptionPrice->getCustomOptionRange(false);
        }

        return $showRange;
    }
}
