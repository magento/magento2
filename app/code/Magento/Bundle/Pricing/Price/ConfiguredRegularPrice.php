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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class ConfiguredRegularPrice extends ConfiguredPrice
{
    /**
<<<<<<< HEAD
     * Price type configured
=======
     * Price type configured.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    const PRICE_CODE = 'configured_regular_price';

    /**
<<<<<<< HEAD
     * Create Selection Price List
=======
     * Create Selection Price List.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return BundleSelectionPrice[]
     */
    protected function createSelectionPriceList(\Magento\Bundle\Model\Option $option): array
    {
        return $this->calculator->createSelectionPriceList($option, $this->product, true);
    }
}
