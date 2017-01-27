<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Interface define methods which control display of "As low as" price
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
