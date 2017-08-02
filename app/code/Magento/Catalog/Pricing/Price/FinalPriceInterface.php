<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

/**
 * Special price interface
 *
 * @api
 * @since 2.0.0
 */
interface FinalPriceInterface
{
    /**
     * Get Minimal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    public function getMinimalPrice();

    /**
     * Get Maximal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    public function getMaximalPrice();
}
