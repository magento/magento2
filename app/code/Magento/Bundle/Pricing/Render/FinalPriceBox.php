<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Render;

use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Catalog\Pricing\Render as CatalogRender;

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
        /** @var FinalPrice $bundlePrice */
        $bundlePrice = $this->getPriceType(FinalPrice::PRICE_CODE);
        $showRange = $bundlePrice->getMinimalPrice() != $bundlePrice->getMaximalPrice();

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
