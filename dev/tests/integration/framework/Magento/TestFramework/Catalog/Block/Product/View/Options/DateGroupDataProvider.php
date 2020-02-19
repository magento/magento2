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
 * Data provider with product custom options from date group(date, date & time, time).
 */
class DateGroupDataProvider
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
            'type_date_required' => [
                [
                    Option::KEY_TITLE => 'Test option date title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-date-title-1',
                ],
                [
                    'block_with_required_class' => '<div class="field date required"',
                    'title' => '<span>Test option date title 1</span>',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_date_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option date title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-date-title-2',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date title 2</span>',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_date_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option date title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-date-title-3',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date title 3</span>',
                    'price' => 'data-price-amount="50"',
                ],
            ],
            'type_date_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option date title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Option::KEY_SKU => 'test-option-date-title-4',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date title 4</span>',
                    'price' => 'data-price-amount="5"',
                ],
            ],
            'type_date_and_time_required' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-date-and-time-title-1',
                ],
                [
                    'block_with_required_class' => '<div class="field date required"',
                    'title' => '<span>Test option date and time title 1</span>',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_date_and_time_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-date-and-time-title-2',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date and time title 2</span>',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_date_and_time_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-date-and-time-title-3',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date and time title 3</span>',
                    'price' => 'data-price-amount="50"',
                ],
            ],
            'type_date_and_time_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option date and time title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Option::KEY_SKU => 'test-option-date-and-time-title-4',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option date and time title 4</span>',
                    'price' => 'data-price-amount="5"',
                ],
            ],
            'type_time_required' => [
                [
                    Option::KEY_TITLE => 'Test option time title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-time-title-1',
                ],
                [
                    'block_with_required_class' => '<div class="field date required"',
                    'title' => '<span>Test option time title 1</span>',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_time_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option time title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-time-title-2',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option time title 2</span>',
                    'price' => 'data-price-amount="10"',
                ],
            ],
            'type_time_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option time title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-time-title-3',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option time title 3</span>',
                    'price' => 'data-price-amount="50"',
                ],
            ],
            'type_time_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option time title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_TIME,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Option::KEY_SKU => 'test-option-time-title-4',
                ],
                [
                    'block_with_required_class' => '<div class="field date"',
                    'title' => '<span>Test option time title 4</span>',
                    'price' => 'data-price-amount="5"',
                ],
            ],
        ];
    }
}
