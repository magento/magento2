<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlConfigurableProduct\Model\Plugin\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter;

/**
 * Post formatting plugin to continue formatting data for configurable type products
 */
class FormatterPlugin
{
    /**
     * Add configurable links and options to configurable types
     *
     * @param Formatter $subject
     * @param array $productData
     * @param Product $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterFormat(Formatter $subject, array $productData, Product $product)
    {
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $extensionAttributes = $product->getExtensionAttributes();
            $productData['configurable_product_options'] = $extensionAttributes->getConfigurableProductOptions();
            $productData['configurable_product_links'] = $extensionAttributes->getConfigurableProductLinks();
        }

        return $productData;
    }
}
