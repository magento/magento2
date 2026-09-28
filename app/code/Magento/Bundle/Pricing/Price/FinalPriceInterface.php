<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface FinalPriceInterface
 * @api
 * @since 100.0.2
 */
interface FinalPriceInterface extends \Magento\Catalog\Pricing\Price\FinalPriceInterface
{
    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getPriceWithoutOption();
}
