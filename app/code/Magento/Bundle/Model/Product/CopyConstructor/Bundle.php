<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;

class Bundle implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * Duplicating bundle options and selections
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate)
    {
        if ($product->getTypeId() != Type::TYPE_BUNDLE) {
            //do nothing if not bundle
            return;
        }

        $bundleOptions = $product->getExtensionAttributes()->getBundleProductOptions() ?: [];
        $duplicatedBundleOptions = [];
        foreach ($bundleOptions as $key => $bundleOption) {
            $duplicatedBundleOptions[$key] = clone $bundleOption;
        }
        $duplicate->getExtensionAttributes()->setBundleProductOptions($duplicatedBundleOptions);
    }
}
