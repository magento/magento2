<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Catalog price interface
 *
 * @api
 */
interface PriceInterface
{
    /**
     * Get price type code
     *
     * @return string
     */
    public function getPriceCode();

    /**
     * Get price value
     *
     * @return float
     */
    public function getValue();

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     */
    public function getAmount();

    /**
     * Get Custom Amount object
     * (specify adjustment code to exclude)
     *
     * @param float $amount
     * @param null|bool|string|array $exclude
     * @param null|array $context
     * @return AmountInterface
     */
    public function getCustomAmount($amount = null, $exclude = null, $context = []);
}
