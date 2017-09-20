<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Quote\Model\Quote;

/**
 * Interface ShippingRateChooserInterface
 * @package Magento\OneTouchOrdering\Model
 */
interface ShippingRateChooserInterface
{
    /**
     * @param Quote $quote
     * @return Quote
     */
    public function choose(Quote $quote): Quote;
}
