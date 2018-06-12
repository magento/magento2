<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CopyConstructorInterface;
use Magento\Catalog\Model\Product\Type;

class Bundle implements CopyConstructorInterface
{
    /**
     * Duplicating bundle options and selections
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate): void
    {
        if ($product->getTypeId() !== Type::TYPE_BUNDLE) {
            //do nothing if not bundle
            return;
        }

        /** @var \Magento\Bundle\Api\Data\OptionInterface[]|null $bundleOptions */
        $bundleOptions = $product->getExtensionAttributes()->getBundleProductOptions() ?: [];

        /** @var \Magento\Bundle\Api\Data\OptionInterface[]|null $duplicatedBundleOptions */
        $duplicatedBundleOptions = [];

        foreach ($bundleOptions as $key => $bundleOption) {
            $duplicatedBundleOptions[$key] = clone $bundleOption;
            $duplicatedBundleOptions[$key]->setOptionId(null);
            /** @var \Magento\Bundle\Api\Data\LinkInterface[]|null $bundleSelections */
            $bundleSelections = $duplicatedBundleOptions[$key]->getProductLinks();
            foreach ($bundleSelections as $bundleSelection) {
                $bundleSelection->setSelectionId(null);
            }
        }
        $duplicate->getExtensionAttributes()->setBundleProductOptions($duplicatedBundleOptions);
    }
}
