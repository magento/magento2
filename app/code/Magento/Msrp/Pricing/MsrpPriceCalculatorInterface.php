<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provide information about MSRP price of a product.
 */
interface MsrpPriceCalculatorInterface
{
    /**
     * Return the value of MSRP product price.
     *
     * @param ProductInterface $product
     * @return float
     */
    public function getMsrpPriceValue(ProductInterface $product): float;
}
