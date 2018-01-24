<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\DownloadableGraphQl\Model\Plugin\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Post formatting plugin to continue formatting data for downloadable type products
 */
class DownloadableOptions implements FormatterInterface
{
    /**
     * Add downloadable options and options to configurable types
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if ($product->getTypeId() === Downloadable::TYPE_DOWNLOADABLE) {
            $extensionAttributes = $product->getExtensionAttributes();
            $productData['downloadable_product_samples'] = $extensionAttributes->getDownloadableProductSamples();
        }

        return $productData;
    }
}
