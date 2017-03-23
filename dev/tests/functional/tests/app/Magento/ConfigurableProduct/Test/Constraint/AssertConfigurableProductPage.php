<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;

/**
 * Class AssertConfigurableProductPage
 * Assert that displayed product data on product page(front-end) equals passed from fixture
 */
class AssertConfigurableProductPage extends AssertProductPage
{
    /**
     * Verify displayed product data on product page(front-end) equals passed from fixture:
     * 1. Product Name
     * 2. Price
     * 3. SKU
     * 4. Description
     * 5. Short Description
     * 6. Attributes
     *
     * @return array
     */
    protected function verify()
    {
        $errors = parent::verify();
        $errors[] = $this->verifyAttributes();

        return array_filter($errors);
    }

    /**
     * Verify displayed product price on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyPrice()
    {
        $priceBlock = $this->productView->getPriceBlock();
        if (!$priceBlock->isVisible()) {
            return "Price block for '{$this->product->getName()}' product' is not visible.";
        }
        $formPrice = $priceBlock->isOldPriceVisible() ? $priceBlock->getOldPrice() : $priceBlock->getPrice();
        $fixturePrice = $this->getLowestConfigurablePrice();

        if ($fixturePrice != $formPrice) {
            return "Displayed product price on product page(front-end) not equals passed from fixture. "
            . "Actual: {$formPrice}, expected: {$fixturePrice}.";
        }
        return null;
    }

    /**
     * Verify displayed product attributes on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyAttributes()
    {
        $attributesData = $this->product->getConfigurableAttributesData();
        $configurableOptions = [];
        $formOptions = $this->productView->getOptions($this->product);

        foreach ($attributesData['attributes_data'] as $attributeKey => $attributeData) {
            $optionData = [
                'title' => $attributeData['frontend_label'],
                'type' => $attributeData['frontend_input'],
                'is_require' => 'Yes',
            ];

            foreach ($attributeData['options'] as $optionKey => $option) {
                $optionData['options'][$optionKey] = [
                    'title' => $option['label'],
                    //Mock price validation
                    'price' => 0
                ];
            }

            $configurableOptions[$attributeKey] = $optionData;
        }

        // Sort data for compare
        $configurableOptions = $this->sortDataByPath($configurableOptions, '::title');
        foreach ($configurableOptions as $key => $configurableOption) {
            $configurableOptions[$key] = $this->sortDataByPath($configurableOption, 'options::title');
        }
        $configurableFormOptions = $formOptions['configurable_options'];
        $configurableFormOptions = $this->sortDataByPath($configurableFormOptions, '::title');
        foreach ($configurableFormOptions as $key => $formOption) {
            $configurableFormOptions[$key] = $this->sortDataByPath($formOption, 'options::title');
        }

        $errors = array_merge(
            //Verify Attribute and options
            $this->verifyData($configurableOptions, $configurableFormOptions, true, false),
            //Verify Attribute options prices
            $this->verifyAttributesMatrix($formOptions['matrix'], $attributesData['matrix'])
        );

        return $errors ? null : $this->prepareErrors($errors, 'Error configurable options:');
    }

    /**
     * Verify displayed product attributes prices on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyAttributesMatrix($variationsMatrix, $generatedMatrix)
    {
        foreach ($generatedMatrix as $key => $value) {
            $generatedMatrix[$key] = array_intersect_key($value, ['price' => 0]);
        }
        return $this->verifyData($generatedMatrix, $variationsMatrix, true, false);
    }

    /**
     * Returns lowest possible price of configurable product.
     *
     * @return string
     */
    protected function getLowestConfigurablePrice()
    {
        $price = null;
        $priceDataConfig = $this->product->getDataFieldConfig('price');
        if (isset($priceDataConfig['source'])) {
            $priceData = $priceDataConfig['source']->getPriceData();
            if (isset($priceData['price_from'])) {
                $price = $priceData['price_from'];
            }
        }

        if (null === $price) {
            $configurableOptions = $this->product->getConfigurableAttributesData();
            foreach ($configurableOptions['matrix'] as $option) {
                $price = $price === null ? $option['price'] : $price;
                if ($price > $option['price']) {
                    $price = $option['price'];
                }
            }
        }
        return $price;
    }
}
