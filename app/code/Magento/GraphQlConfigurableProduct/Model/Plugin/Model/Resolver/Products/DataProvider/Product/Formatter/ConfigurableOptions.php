<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlConfigurableProduct\Model\Plugin\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Post formatting plugin to continue formatting data for configurable type products
 */
class ConfigurableOptions implements FormatterInterface
{
    /**
     * Add configurable links and options to configurable types
     *
     * {@inheritdoc}
     */
    public function format(ProductInterface $product, array $productData = [])
    {
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $extensionAttributes = $product->getExtensionAttributes();
            $productData['configurable_product_options'] = $extensionAttributes->getConfigurableProductOptions();
            $productData['configurable_product_links'] = $extensionAttributes->getConfigurableProductLinks();
        }

        return $productData;
    }
}
