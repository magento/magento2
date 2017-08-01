<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Grouped product type implementation for backend
 * @since 2.0.0
 */
class Backend extends \Magento\GroupedProduct\Model\Product\Type\Grouped
{
    /**
     * No filters required in backend
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return \Magento\GroupedProduct\Model\Product\Type\Grouped
     * @since 2.0.0
     */
    public function setSaleableStatus($product)
    {
        return $this;
    }
}
