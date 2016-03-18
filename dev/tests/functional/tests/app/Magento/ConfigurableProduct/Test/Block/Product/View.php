<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\ConfigurableProduct\Test\Block\Product\View\ConfigurableOptions;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class View
 * Product view block on frontend page
 */
class View extends \Magento\Catalog\Test\Block\Product\View
{
    /**
     * Get configurable options block
     *
     * @return ConfigurableOptions
     */
    public function getConfigurableOptionsBlock()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Product\View\ConfigurableOptions',
            ['element' => $this->_rootElement]
        );
    }

    /**
     * Fill in the option specified for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        /** @var ConfigurableProduct $product */
        $attributesData = $product->getConfigurableAttributesData()['attributes_data'];
        $checkoutData = $product->getCheckoutData();

        // Prepare attribute data
        foreach ($attributesData as $attributeKey => $attribute) {
            $attributesData[$attributeKey] = [
                'type' => $attribute['frontend_input'],
                'title' => $attribute['label'],
                'options' => [],
            ];

            foreach ($attribute['options'] as $optionKey => $option) {
                $attributesData[$attributeKey]['options'][$optionKey] = [
                    'title' => $option['label'],
                ];
            }
            $attributesData[$attributeKey]['options'] = array_values($attributesData[$attributeKey]['options']);
        }
        $attributesData = array_values($attributesData);

        $configurableCheckoutData = isset($checkoutData['options']['configurable_options'])
            ? $checkoutData['options']['configurable_options']
            : [];
        $checkoutOptionsData = $this->prepareCheckoutData($attributesData, $configurableCheckoutData);
        $this->getCustomOptionsBlock()->fillCustomOptions($checkoutOptionsData);

        parent::fillOptions($product);
    }

    /**
     * Return product options
     *
     * @param FixtureInterface $product [optional]
     * @return array
     */
    public function getOptions(FixtureInterface $product = null)
    {
        $options = [
            'configurable_options' => $this->getConfigurableOptionsBlock()->getOptions($product),
        ];
        $options += parent::getOptions($product);

        return $options;
    }
}
