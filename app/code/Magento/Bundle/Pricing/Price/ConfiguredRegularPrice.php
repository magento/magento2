<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

/**
<<<<<<< HEAD
 * Configured regular price model.
=======
 * Configured regular price model
>>>>>>> upstream/2.2-develop
 */
class ConfiguredRegularPrice extends ConfiguredPrice
{
    /**
<<<<<<< HEAD
     * Price type configured.
=======
     * Price type configured
>>>>>>> upstream/2.2-develop
     */
    const PRICE_CODE = 'configured_regular_price';

    /**
<<<<<<< HEAD
     * Create Selection Price List.
=======
     * Create Selection Price List
>>>>>>> upstream/2.2-develop
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return BundleSelectionPrice[]
     */
    protected function createSelectionPriceList(\Magento\Bundle\Model\Option $option): array
    {
        return $this->calculator->createSelectionPriceList($option, $this->product, true);
    }
}
