<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type;

use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\AbstractBase;

/**
 * Abstract data provider for options from text group.
 */
abstract class AbstractText extends AbstractBase
{
    /**
     * @inheritdoc
     */
    public static function getDataForCreateOptions(): array
    {
        return array_merge_recursive(
            parent::getDataForCreateOptions(),
            [
                "type_{static::getType()}_options_with_max_charters_configuration" => [
                    [
                        'record_id' => 0,
                        'sort_order' => 1,
                        'is_require' => 1,
                        'sku' => 'test-option-title-1',
                        'max_characters' => 30,
                        'title' => 'Test option title 1',
                        'type' => static::getType(),
                        'price' => 10,
                        'price_type' => 'fixed',
                    ],
                ],
                "type_{static::getType()}_options_without_max_charters_configuration" => [
                    [
                        'record_id' => 0,
                        'sort_order' => 1,
                        'is_require' => 1,
                        'sku' => 'test-option-title-1',
                        'title' => 'Test option title 1',
                        'type' => static::getType(),
                        'price' => 10,
                        'price_type' => 'fixed',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDataForUpdateOptions(): array
    {
        return array_merge_recursive(
            parent::getDataForUpdateOptions(),
            [
                "type_{static::getType()}_options_with_max_charters_configuration" => [
                    [
                        'max_characters' => 0,
                    ],
                ],
                "type_{static::getType()}_options_without_max_charters_configuration" => [
                    [
                        'max_characters' => 55,
                    ],
                ],
            ]
        );
    }
}
