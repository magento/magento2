<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;

/**
 * Backend catalog product grid.
 */
class Grid extends \Magento\Catalog\Test\Block\Adminhtml\Product\Grid
{
    /**
     * Prepare data.
     *
     * @param ConfigurableProduct $product
     * @return array
     */
    public function prepareData($product)
    {
        $configurableAttributesData = $product->getConfigurableAttributesData();
        $productItems[] = ['sku' => $product->getSku()];
        foreach ($configurableAttributesData['matrix'] as $variation) {
            $productItems[] = ['sku' => $variation['sku']];
        }

        return $productItems;
    }
}
