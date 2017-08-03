<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Pricing\Price;

use Magento\Catalog\Model\Product;

/**
 * MSRP price interface
 *
 * @api
 * @since 2.0.0
 */
interface MsrpPriceInterface
{
    /**
     * Check is product need gesture to show price
     *
     * @return bool
     * @since 2.0.0
     */
    public function isShowPriceOnGesture();

    /**
     * Get Msrp message for price
     *
     * @return string
     * @since 2.0.0
     */
    public function getMsrpPriceMessage();

    /**
     * Check if Minimum Advertised Price is enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isMsrpEnabled();

    /**
     * Check if can apply Minimum Advertise price to product in specific visibility
     *
     * @param Product $saleableItem
     * @return bool
     * @since 2.0.0
     */
    public function canApplyMsrp(Product $saleableItem);
}
