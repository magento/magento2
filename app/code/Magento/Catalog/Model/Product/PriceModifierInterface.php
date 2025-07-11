<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;

/**
 * @api
 * @since 100.0.2
 */
interface PriceModifierInterface
{
    /**
     * Modify price
     *
     * @param mixed $price
     * @param Product $product
     * @return mixed
     */
    public function modifyPrice($price, Product $product);
}
