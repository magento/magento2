<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

/**
<<<<<<< HEAD
 * Configured regular price model
=======
 * Configured regular price model.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class ConfiguredRegularPrice extends ConfiguredPrice
{
    /**
<<<<<<< HEAD
     * Price type configured
=======
     * Price type configured.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    const PRICE_CODE = 'configured_regular_price';

    /**
<<<<<<< HEAD
     * Create Selection Price List
=======
     * Create Selection Price List.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return BundleSelectionPrice[]
     */
    protected function createSelectionPriceList(\Magento\Bundle\Model\Option $option): array
    {
        return $this->calculator->createSelectionPriceList($option, $this->product, true);
    }
}
