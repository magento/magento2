<?php
/**
 * Price calculation extension point
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;

interface PriceModifierInterface
{
    /**
     * Modify price
     *
     * @param mixed $price
     * @param Product $product
     * @return mixed
     * @api
     */
    public function modifyPrice($price, Product $product);
}
