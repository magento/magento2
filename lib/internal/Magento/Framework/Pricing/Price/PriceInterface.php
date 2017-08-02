<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Catalog price interface
 *
 * @api
 * @since 2.0.0
 */
interface PriceInterface
{
    /**
     * Get price type code
     *
     * @return string
     * @since 2.0.0
     */
    public function getPriceCode();

    /**
     * Get price value
     *
     * @return float
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCustomAmount($amount = null, $exclude = null, $context = []);
}
