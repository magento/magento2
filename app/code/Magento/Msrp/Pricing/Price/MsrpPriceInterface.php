<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Pricing\Price;

use Magento\Catalog\Model\Product;

/**
 * MSRP price interface
 */
interface MsrpPriceInterface
{
    /**
     * Check is product need gesture to show price
     *
     * @return bool
     */
    public function isShowPriceOnGesture();

    /**
     * Get Msrp message for price
     *
     * @return string
     */
    public function getMsrpPriceMessage();

    /**
     * Check if Minimum Advertised Price is enabled
     *
     * @return bool
     */
    public function isMsrpEnabled();

    /**
     * Check if can apply Minimum Advertise price to product in specific visibility
     *
     * @param Product $saleableItem
     * @return bool
     */
    public function canApplyMsrp(Product $saleableItem);
}
