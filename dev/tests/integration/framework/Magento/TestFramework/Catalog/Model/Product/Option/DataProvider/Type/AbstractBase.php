<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type;

/**
 * Base custom options data provider.
 */
abstract class AbstractBase
{
    /**
     * Return data for create options for all cases.
     *
     * @return array
     */
    public static function getDataForCreateOptions(): array
    {
        return [
            "type_{static::getType()}_title" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => static::getType(),
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            "type_{static::getType()}_required_options" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => static::getType(),
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            "type_{static::getType()}_not_required_options" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => static::getType(),
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            "type_{static::getType()}_options_with_fixed_price" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => static::getType(),
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            "type_{static::getType()}_options_with_percent_price" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => static::getType(),
                    'price' => 10,
                    'price_type' => 'percent',
                ],
            ],
            "type_{static::getType()}_price" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => static::getType(),
                    'price' => 22,
                    'price_type' => 'percent',
                ],
            ],
            "type_{static::getType()}_sku" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => static::getType(),
                    'price' => 22,
                    'price_type' => 'percent',
                ],
            ],
        ];
    }

    /**
     * Return data for create options for all cases.
     *
     * @return array
     */
    public static function getDataForUpdateOptions(): array
    {
        return array_merge_recursive(
            static::getDataForCreateOptions(),
            [
                "type_{static::getType()}_title" => [
                    [
                        'title' => 'Test updated option title',
                    ]
                ],
                "type_{static::getType()}_required_options" => [
                    [
                        'is_require' => 0,
                    ],
                ],
                "type_{static::getType()}_not_required_options" => [
                    [
                        'is_require' => 1,
                    ],
                ],
                "type_{static::getType()}_options_with_fixed_price" => [
                    [
                        'price_type' => 'percent',
                    ],
                ],
                "type_{static::getType()}_options_with_percent_price" => [
                    [
                        'price_type' => 'fixed',
                    ],
                ],
                "type_{static::getType()}_price" => [
                    [
                        'price' => 60,
                    ],
                ],
                "type_{static::getType()}_sku" => [
                    [
                        'sku' => 'Updated option sku',
                    ],
                ],
            ]
        );
    }

    /**
     * Return option type.
     *
     * @return string
     */
    abstract protected static function getType(): string;
}
