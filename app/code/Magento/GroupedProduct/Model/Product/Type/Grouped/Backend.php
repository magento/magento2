<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
