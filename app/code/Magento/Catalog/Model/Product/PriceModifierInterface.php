<?php
/**
 * Price calculation extension point
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;

/**
 * @api
 * @since 2.0.0
 */
interface PriceModifierInterface
{
    /**
     * Modify price
     *
     * @param mixed $price
     * @param Product $product
     * @return mixed
     * @api
     * @since 2.0.0
     */
    public function modifyPrice($price, Product $product);
}
