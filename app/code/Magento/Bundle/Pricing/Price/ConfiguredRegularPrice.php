<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

/**
 * Configured regular price model.
 */
class ConfiguredRegularPrice extends ConfiguredPrice
{
    /**
     * Price type configured.
     */
    const PRICE_CODE = 'configured_regular_price';

    /**
     * Create Selection Price List.
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return BundleSelectionPrice[]
     */
    protected function createSelectionPriceList(\Magento\Bundle\Model\Option $option): array
    {
        return $this->calculator->createSelectionPriceList($option, $this->product, true);
    }
}
