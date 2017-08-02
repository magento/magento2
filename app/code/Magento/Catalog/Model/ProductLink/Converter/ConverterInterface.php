<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink\Converter;

/**
 * Interface \Magento\Catalog\Model\ProductLink\Converter\ConverterInterface
 *
 * @since 2.0.0
 */
interface ConverterInterface
{
    /**
     * Convert product to array representation
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @since 2.0.0
     */
    public function convert(\Magento\Catalog\Model\Product $product);
}
