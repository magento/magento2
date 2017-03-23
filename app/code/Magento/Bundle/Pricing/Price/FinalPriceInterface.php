<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

/**
 * Interface FinalPriceInterface
 * @api
 */
interface FinalPriceInterface extends \Magento\Catalog\Pricing\Price\FinalPriceInterface
{
    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getPriceWithoutOption();
}
