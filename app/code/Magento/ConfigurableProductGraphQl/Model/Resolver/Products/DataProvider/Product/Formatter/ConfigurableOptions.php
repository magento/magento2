<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Post formatting to continue formatting data for configurable type products
 */
class ConfigurableOptions implements FormatterInterface
{
    /**
     * @var Configurable
     */
    private $configurableData;

    /**
     * @param Configurable $configurableData
     */
    public function __construct(Configurable $configurableData)
    {
        $this->configurableData = $configurableData;
    }

    /**
     * Add configurable links and options to configurable types
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $extensionAttributes = $product->getExtensionAttributes();
            $productData['configurable_product_options'] = $extensionAttributes->getConfigurableProductOptions();
            $productData['configurable_product_links'] = $extensionAttributes->getConfigurableProductLinks();
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $option */
            foreach ($productData['configurable_product_options'] as $optionKey => $option) {
                $productData['configurable_product_options'][$optionKey]['attribute_code']
                    = $option->getProductAttribute()->getAttributeCode();
            }
        }

        return $productData;
    }
}
