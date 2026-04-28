<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Pricing\Adjustment;

use Magento\Framework\Pricing\SaleableInterface;

/**
 * Calculator interface
 *
 * @api
 * @since 100.0.2
 */
interface CalculatorInterface
{
    /**
     * @param float|string $amount
     * @param SaleableInterface $saleableItem
     * @param null|bool|string|array $exclude
     * @param null|array $context
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount($amount, SaleableInterface $saleableItem, $exclude = null, $context = []);
}
