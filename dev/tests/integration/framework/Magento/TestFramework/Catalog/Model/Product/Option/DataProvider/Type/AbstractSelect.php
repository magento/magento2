<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type;

use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\AbstractBase;

/**
 * Abstract data provider for options from select group.
 */
abstract class AbstractSelect extends AbstractBase
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDataForCreateOptions(): array
    {
        return [
            "type_{$this->getType()}_title" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option title 1',
                    'type' => $this->getType(),
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            "type_{$this->getType()}_required_options" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option title 1',
                    'type' => $this->getType(),
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            "type_{$this->getType()}_not_required_options" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'title' => 'Test option title 1',
                    'type' => $this->getType(),
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            "type_{$this->getType()}_options_with_fixed_price" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option title 1',
                    'type' => $this->getType(),
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            "type_{$this->getType()}_options_with_percent_price" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option title 1',
                    'type' => $this->getType(),
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'percent',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            "type_{$this->getType()}_price" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option title 1',
                    'type' => $this->getType(),
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 22,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            "type_{$this->getType()}_sku" => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option title 1',
                    'type' => $this->getType(),
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDataForUpdateOptions(): array
    {
        return array_merge_recursive(
            $this->getDataForCreateOptions(),
            [
                "type_{$this->getType()}_title" => [
                    [
                        'title' => 'Updated test option title 1',
                    ],
                    [],
                ],
                "type_{$this->getType()}_required_options" => [
                    [
                        'is_require' => 0,
                    ],
                    [],
                ],
                "type_{$this->getType()}_not_required_options" => [
                    [
                        'is_require' => 1,
                    ],
                    [],
                ],
                "type_{$this->getType()}_options_with_fixed_price" => [
                    [],
                    [
                        'price_type' => 'percent',
                    ],
                ],
                "type_{$this->getType()}_options_with_percent_price" => [
                    [],
                    [
                        'price_type' => 'fixed',
                    ],
                ],
                "type_{$this->getType()}_price" => [
                    [],
                    [
                        'price' => 666,
                    ],
                ],
                "type_{$this->getType()}_sku" => [
                    [],
                    [
                        'sku' => 'updated-test-option-1-value-1',
                    ],
                ],
            ]
        );
    }
}
