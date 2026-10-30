<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\ProductLink\Converter;

/**
 * Interface \Magento\Catalog\Model\ProductLink\Converter\ConverterInterface
 *
 * @api
 */
interface ConverterInterface
{
    /**
     * Convert product to array representation
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function convert(\Magento\Catalog\Model\Product $product);
}
