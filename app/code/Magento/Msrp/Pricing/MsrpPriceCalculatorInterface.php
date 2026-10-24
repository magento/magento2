<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Msrp\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provide information about MSRP price of a product.
 *
 * @api
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
