<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Quote\Model\Quote;

/**
 * Interface ShippingRateChooserInterface
 */
interface ShippingRateChooserRuleInterface
{
    /**
     * @param array $shippingRates
     * @return string
     */
    public function choose(array $shippingRates): string;
}
