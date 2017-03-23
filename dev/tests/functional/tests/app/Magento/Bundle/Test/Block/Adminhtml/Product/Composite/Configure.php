<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Product\Composite;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Adminhtml bundle product composite configure block.
 */
class Configure extends \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure
{
    /**
     * Locator for bundle fields root element.
     *
     * @var string
     */
    protected $bundleFieldsRootElement = '//*[@id="catalog_product_composite_configure_fields_bundle"]';

    /**
     * Option selector.
     *
     * @var string
     */
    protected $option = '//label[.="%option_name%"]//following-sibling::*//%selector%';

    /**
     * Fill options for the product.
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
     * Prepare data.
     *
     * @param array $fields
     * @return array
     */
    protected function prepareData(array $fields)
    {
        $productOptions = [];
        if (!empty($fields['checkout_data']['options']['bundle_options'])) {
            foreach ($fields['checkout_data']['options']['bundle_options'] as $key => $option) {
                $productOptions['option_' . $key] = $this->prepareOptionMapping($option);
            }
        }

        return $productOptions;
    }

    /**
     * Prepare option mapping.
     *
     * @param array $option
     * @return array
     */
    protected function prepareOptionMapping(array $option)
    {
        $type = $this->prepareOptionType($option['type']);
        $mapping = $this->dataMapping([$type => '']);
        $mapping[$type]['selector'] = $this->prepareOptionSelector($option, $mapping[$type]['selector']);
        $mapping[$type]['value'] = $this->isCheckbox($type) ? 'Yes' : $option['value']['name'];

        return $mapping[$type];
    }

    /**
     * Prepare option type.
     *
     * @param string $optionType
     * @return string
     */
    protected function prepareOptionType($optionType)
    {
        return strtolower(preg_replace('/[^a-zA-Z]/', '', $optionType));
    }

    /**
     * Check element of checkbox type.
     *
     * @param string $type
     * @return bool
     */
    protected function isCheckbox($type)
    {
        return $type == 'checkbox' || $type == 'radiobutton';
    }

    /**
     * Prepare selector for option.
     *
     * @param array $option
     * @param string $selector
     * @return string
     */
    protected function prepareOptionSelector(array $option, $selector)
    {
        return $this->bundleFieldsRootElement . str_replace(
            '%selector%',
            str_replace('%product_name%', $option['value']['name'], $selector),
            str_replace('%option_name%', $option['title'], $this->option)
        );
    }
}
