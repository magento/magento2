<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product\CopyConstructor;

class Bundle implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * Duplicating bundle options and selections
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            //do nothing if not bundle
            return;
        }

        $bundleOptions = $product->getExtensionAttributes()->getBundleProductOptions();
        $duplicate->getExtensionAttributes()->setBundleProductOptions($bundleOptions);
    }
}
