<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Render;

use Magento\Bundle\Pricing\Price;
use Magento\Catalog\Pricing\Render as CatalogRender;

/**
 * Class for final_price rendering
 */
class FinalPriceBox extends CatalogRender\FinalPriceBox
{
    /**
     * Check if bundle product has one more custom option with different prices
     *
     * @return bool
     */
    public function showRangePrice()
    {
        /** @var Price\BundleOptionPrice $optionPrice */
        $optionPrice = $this->getPriceType(Price\BundleOptionPrice::PRICE_CODE);
        return $optionPrice->getValue() !== $optionPrice->getMaxValue();
    }
}
