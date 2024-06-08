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

/**
 * Data provider with product custom options from text group(field, area).
 */
class TextGroupDataProvider
{
    /**
     * Return options data.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public static function getData(): array
    {
        return [
            'type_field_required' => [
                [
                    Option::KEY_TITLE => 'Test option field title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-field-title-1',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field required">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 1</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<input type="text"/',
                ],
            ],
            'type_field_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option field title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-field-title-2',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 2</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<input type="text"/',
                ],
            ],
            'type_field_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option field title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-field-title-3',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 3</span>',
                    'price' => 'data-price-amount="50"',
                    'required_element' => '/<input type="text"/',
                ],
            ],
            'type_field_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option field title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Option::KEY_SKU => 'test-option-field-title-4',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 4</span>',
                    'price' => 'data-price-amount="5"',
                    'required_element' => '/<input type="text"/',
                ],
            ],
            'type_field_max_characters' => [
                [
                    Option::KEY_TITLE => 'Test option field title 5',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-field-title-5',
                    Option::KEY_MAX_CHARACTERS => 99,
                ],
                [
                    'block_with_required_class' => '<div class="field">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option field title 5</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<input type="text"/',
                    'max_characters' => 'Maximum 99 characters',
                ],
            ],
            'type_area_required' => [
                [
                    Option::KEY_TITLE => 'Test option area title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-area-title-1',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea required">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 1</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<textarea/',
                ],
            ],
            'type_area_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option area title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-area-title-2',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 2</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<textarea/',
                ],
            ],
            'type_area_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option area title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-area-title-3',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 3</span>',
                    'price' => 'data-price-amount="50"',
                    'required_element' => '/<textarea/',
                ],
            ],
            'type_area_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option area title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Option::KEY_SKU => 'test-option-area-title-4',
                    Option::KEY_MAX_CHARACTERS => 0,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 4</span>',
                    'price' => 'data-price-amount="5"',
                    'required_element' => '/<textarea/',
                ],
            ],
            'type_area_max_characters' => [
                [
                    Option::KEY_TITLE => 'Test option area title 5',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-area-title-5',
                    Option::KEY_MAX_CHARACTERS => 99,
                ],
                [
                    'block_with_required_class' => '<div class="field textarea">',
                    'label_for_created_option' => '<label class="label" for="options_%s_text">',
                    'title' => '<span>Test option area title 5</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<textarea/',
                    'max_characters' => 'Maximum 99 characters',
                ],
            ],
        ];
    }
}
