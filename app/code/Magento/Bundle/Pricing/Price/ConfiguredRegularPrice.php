<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Pricing\Price;

/**
 * Configured regular price model
 * @api
 * @since 100.0.2
 */
class ConfiguredRegularPrice extends ConfiguredPrice
{
    /**
     * Price type configured
     */
    const PRICE_CODE = 'configured_regular_price';

    /**
     * Create Selection Price List
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param bool $useRegularPrice
     * @return BundleSelectionPrice[]
     */
    protected function createSelectionPriceList($option)
    {
        return $this->calculator->createSelectionPriceList($option, $this->product, true);
    }
}
