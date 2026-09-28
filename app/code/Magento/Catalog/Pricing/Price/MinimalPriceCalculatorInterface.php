<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Interface define methods which control display of "As low as" price
 *
 * @api
 */
interface MinimalPriceCalculatorInterface
{
    /**
     * Get raw value for "as low as" price
     *
     * @param SaleableInterface $saleableItem
     * @return float|null
     */
    public function getValue(SaleableInterface $saleableItem);

    /**
     * Return structured object with "as low as" value
     *
     * @param SaleableInterface $saleableItem
     * @return AmountInterface|null
     */
    public function getAmount(SaleableInterface $saleableItem);
}
