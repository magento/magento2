<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class CheapestShippingRateChooserRule implements ShippingRateChooserRuleInterface
{
    /**
     * @param array $shippingRates
     * @return string
     */
    public function choose(array $shippingRates): string
    {
        $rate = array_shift($shippingRates);
        foreach ($shippingRates as $tmpRate) {
            if ($tmpRate['price'] < $rate['price']) {
                $rate = $tmpRate;
            }
        }

        return $rate['code'];
    }
}
