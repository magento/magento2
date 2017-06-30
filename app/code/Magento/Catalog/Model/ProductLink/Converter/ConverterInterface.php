<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink\Converter;

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
