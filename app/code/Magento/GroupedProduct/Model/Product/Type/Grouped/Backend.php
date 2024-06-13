<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Grouped product type implementation for backend
 */
class Backend extends \Magento\GroupedProduct\Model\Product\Type\Grouped
{
    /**
     * No filters required in backend
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    public function setSaleableStatus($product)
    {
        return $this;
    }
}
