<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface FinalPriceInterface
 * @api
 * @since 2.0.0
 */
interface FinalPriceInterface extends \Magento\Catalog\Pricing\Price\FinalPriceInterface
{
    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    public function getPriceWithoutOption();
}
