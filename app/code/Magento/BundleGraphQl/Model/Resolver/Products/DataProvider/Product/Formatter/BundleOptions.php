<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Post formatting data to set option for bundle product
 */
class BundleOptions implements FormatterInterface
{
    /**
     * Add bundle options and options to configurable types
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if ($product->getTypeId() === Bundle::TYPE_CODE) {
            $extensionAttributes = $product->getExtensionAttributes();
            $productData['bundle_product_options'] = $extensionAttributes->getBundleProductOptions();
        }

        return $productData;
    }
}
