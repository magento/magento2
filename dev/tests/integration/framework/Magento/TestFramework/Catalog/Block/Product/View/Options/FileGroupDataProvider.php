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
 * Data provider with product custom options from file group(file).
 */
class FileGroupDataProvider
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
            'type_file_required' => [
                [
                    Option::KEY_TITLE => 'Test option file title 1',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 1,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-file-title-1',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file required">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 1</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<input type="file"/',
                    'file_extension' => '<strong>png, jpg</strong>',
                ],
            ],
            'type_file_not_required' => [
                [
                    Option::KEY_TITLE => 'Test option file title 2',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 10,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-file-title-2',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 2</span>',
                    'price' => 'data-price-amount="10"',
                    'required_element' => '/<input type="file"/',
                    'file_extension' => '<strong>png, jpg</strong>',
                ],
            ],
            'type_file_fixed_price' => [
                [
                    Option::KEY_TITLE => 'Test option file title 3',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-file-title-3',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 3</span>',
                    'price' => 'data-price-amount="50"',
                    'required_element' => '/<input type="file"/',
                    'file_extension' => '<strong>png, jpg</strong>',
                ],
            ],
            'type_file_percent_price' => [
                [
                    Option::KEY_TITLE => 'Test option file title 4',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
                    Option::KEY_SKU => 'test-option-file-title-4',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 4</span>',
                    'price' => 'data-price-amount="5"',
                    'required_element' => '/<input type="file"/',
                    'file_extension' => '<strong>png, jpg</strong>',
                ],
            ],
            'type_file_with_width_and_height' => [
                [
                    Option::KEY_TITLE => 'Test option file title 5',
                    Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    Option::KEY_IS_REQUIRE => 0,
                    Option::KEY_PRICE => 50,
                    Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
                    Option::KEY_SKU => 'test-option-file-title-5',
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_FILE_EXTENSION => 'png, jpg',
                    Option::KEY_IMAGE_SIZE_X => 10,
                    Option::KEY_IMAGE_SIZE_Y => 81,
                ],
                [
                    'block_with_required_class' => '<div class="field file">',
                    'label_for_created_option' => '<label class="label" for="options_%s_file"',
                    'title' => '<span>Test option file title 5</span>',
                    'price' => 'data-price-amount="50"',
                    'required_element' => '/<input type="file"/',
                    'file_extension' => '<strong>png, jpg</strong>',
                    'file_width' => '/%s:.*<strong>10 px.<\/strong>/',
                    'file_height' => '/%s:.*<strong>81 px.<\/strong>/',
                ],
            ],
        ];
    }
}
