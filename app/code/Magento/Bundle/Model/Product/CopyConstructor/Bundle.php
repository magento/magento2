<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;

/**
 * Provides duplicating bundle options and selections
 */
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
            $duplicatedBundleOption = clone $bundleOption;
            /**
             * Set option and selection ids to 'null' in order to create new option(selection) for duplicated product,
             * but not modifying existing one, which led to lost of option(selection) in original product.
             */
            $productLinks = [];
            foreach ($duplicatedBundleOption->getProductLinks() ?: [] as $productLink) {
                $productLinkDuplicate = clone $productLink;
                $productLinkDuplicate->setId(null);
                $productLinkDuplicate->setSelectionId(null);
                $productLinkDuplicate->setOptionId(null);
                $productLinks[] = $productLinkDuplicate;
            }
            $duplicatedBundleOption->setProductLinks($productLinks);
            $duplicatedBundleOption->setOptionId(null);
            $duplicatedBundleOptions[$key] = $duplicatedBundleOption;
        }
        $duplicate->getExtensionAttributes()->setBundleProductOptions($duplicatedBundleOptions);
    }
}
