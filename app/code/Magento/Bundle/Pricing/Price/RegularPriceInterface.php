<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Regular price interface
 * @api
 * @since 2.0.0
 */
interface RegularPriceInterface extends \Magento\Framework\Pricing\Price\BasePriceProviderInterface
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
