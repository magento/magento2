<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Block\Product\View\Options;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;

/**
 * Data provider with product custom options from select group(drop-down, radio buttons, checkbox, multiple select).
 */
class SelectGroupDataProvider
{
    /**
     * Return options data.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            'type_drop_down_required' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-drop-down-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 1</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ],
            ],
            'type_drop_down_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-drop-down-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 2</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ],
            ],
            'type_drop_down_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-drop-down-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 3</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="50" >%s \+\s{11}\$50.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ],
            ],
            'type_drop_down_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option drop-down title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option drop-down title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Value::KEY_SKU => 'test-option-drop-down-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option drop-down title 4</span>',
                    'required_element' => '/<select/',
                    'option_value_item' => '/<option value="%s"  price="5" >%s \+\s{11}\$5.00.*/',
                    'not_contain_arr' => [
                        '/<select.*multiple="multiple"/',
                    ],
                ],
            ],
            'type_radio_button_required' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-radio-button-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 1</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_radio_button_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-radio-button-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 2</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_radio_button_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-radio-button-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 3</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="50"',
                ],
            ],
            'type_radio_button_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option radio-button title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_RADIO,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option radio-button title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Value::KEY_SKU => 'test-option-radio-button-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option radio-button title 4</span>',
                    'required_element' => '/<input type="radio"/',
                    'price' => 'data-price-amount="5"',
                ],
            ],
            'type_checkbox_required' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-checkbox-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 1</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_checkbox_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-checkbox-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 2</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_checkbox_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-checkbox-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 3</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="50"',
                ],
            ],
            'type_checkbox_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option checkbox title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option checkbox title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Value::KEY_SKU => 'test-option-checkbox-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option checkbox title 4</span>',
                    'required_element' => '/<input type="checkbox"/',
                    'price' => 'data-price-amount="5"',
                ],
            ],
            'type_multiselect_required' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 1,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 1 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-multiselect-title-1-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 1</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                ],
            ],
            'type_multiselect_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 2 value 1',
                    Value::KEY_PRICE => 10,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-multiselect-title-2-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 2</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="10" >%s \+\s{11}\$10.00.*/',
                ],
            ],
            'type_multiselect_value_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 3 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Value::KEY_SKU => 'test-option-multiselect-title-3-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 3</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="50" >%s \+\s{11}\$50.00.*/',
                ],
            ],
            'type_multiselect_value_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option multiselect title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    Option::KEY_IS_REQUIRE => 0,
                ],
                [
                    Value::KEY_TITLE => 'Test option multiselect title 4 value 1',
                    Value::KEY_PRICE => 50,
                    Value::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Value::KEY_SKU => 'test-option-multiselect-title-4-value-1',
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="select_%s">',
                    'title' => '<span>Test option multiselect title 4</span>',
                    'required_element' => '/<select.*multiple="multiple"/',
                    'option_value_item' => '/<option value="%s"  price="5" >%s \+\s{11}\$5.00.*/',
                ],
            ],
        ];
    }
}
