<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Composite;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Configure
 * Adminhtml configurable product composite configure block
 */
class Configure extends \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure
{
    /**
     * Fill options for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $data = $this->prepareData($product->getData());
        $this->_fill($data);
    }

    /**
     * Prepare data
     *
     * @param array $fields
     * @return array
     */
    protected function prepareData(array $fields)
    {
        $productOptions = [];
        $checkoutData = $fields['checkout_data']['options'];

        if (!empty($checkoutData['configurable_options'])) {
            $configurableAttributesData = $fields['configurable_attributes_data']['attributes_data'];
            $attributeMapping = $this->dataMapping(['attribute' => '']);
            $selector = $attributeMapping['attribute']['selector'];
            foreach ($checkoutData['configurable_options'] as $key => $optionData) {
                $attribute = $configurableAttributesData[$optionData['title']];
                $attributeMapping['attribute']['selector'] = sprintf($selector, $attribute['label']);
                $attributeMapping['attribute']['value'] = $attribute['options'][$optionData['value']]['label'];
                $productOptions['attribute_' . $key] = $attributeMapping['attribute'];
            }
        }

        return $productOptions;
    }
}
