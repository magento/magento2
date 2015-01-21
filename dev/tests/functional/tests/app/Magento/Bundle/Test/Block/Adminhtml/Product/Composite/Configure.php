<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Product\Composite;

use Mtf\Fixture\FixtureInterface;

/**
 * Class Configure
 * Adminhtml bundle product composite configure block
 */
class Configure extends \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure
{
    /**
     * Option selector
     *
     * @var string
     */
    protected $option = '//div[@class="fields options"]//label[.="%option_name%"]//following-sibling::*//%selector%';

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

        if (!empty($checkoutData['bundle_options'])) {
            foreach ($checkoutData['bundle_options'] as $key => $option) {
                $type = strtolower(preg_replace('/[^a-zA-Z]/', '', $option['type']));
                $optionMapping = $this->dataMapping([$type => '']);

                $optionMapping[$type]['selector'] = str_replace(
                    '%selector%',
                    str_replace('%product_name%', $option['value']['name'], $optionMapping[$type]['selector']),
                    str_replace('%option_name%', $option['title'], $this->option)
                );

                $optionMapping[$type]['value'] = ($type == 'checkbox' || $type == 'radiobutton')
                    ? 'Yes'
                    : $option['value']['name'];

                $productOptions['option_' . $key] = $optionMapping[$type];
            }
        }

        return $productOptions;
    }
}
