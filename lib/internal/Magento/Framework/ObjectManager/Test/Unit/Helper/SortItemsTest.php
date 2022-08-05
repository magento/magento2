<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Helper;

use Magento\Framework\ObjectManager\Helper\SortItems as SortItemsHelper;
use PHPUnit\Framework\TestCase;

class SortItemsTest extends TestCase
{
    /**
     * @var MockObject|InterpreterInterface
     */
    protected InterpreterInterface $_itemInterpreter;

    /**
     * @var SortItemsHelper
     */
    protected SortItemsHelper $_model;

    protected function setUp(): void
    {
        $this->_model = new SortItemsHelper();
    }

    /**
     * @param array $input
     * @param array $expected
     *
     * @dataProvider evaluateDataProvider
     */
    public function testSortItems(array $input, array $expected): void
    {
        $actual = $this->_model->sortItems($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function evaluateDataProvider(): array
    {
        return [
            'empty array items' => [
                [],
                [],
            ],
            'absent array items' => [
                [],
                [],
            ],
            'present array items' => [
                [
                    'key1' => ['value' => 'value 1'],
                    'key2' => ['value' => 'value 2'],
                    'key3' => ['value' => 'value 3'],
                ],
                [
                    'key1' => ['value' => 'value 1'],
                    'key2' => ['value' => 'value 2'],
                    'key3' => ['value' => 'value 3'],
                ],
            ],
            'sorted array items' => [
                    [
                        'key1' => ['value' => 'value 1', 'sortOrder' => 50],
                        'key2' => ['value' => 'value 2'],
                        'key3' => ['value' => 'value 3', 'sortOrder' => 10],
                        'key4' => ['value' => 'value 4'],
                    ],
                     [
                        'key2' => ['value' => 'value 2'],
                        'key4' => ['value' => 'value 4'],
                        'key3' => ['value' => 'value 3', 'sortOrder' => 10],
                        'key1' => ['value' => 'value 1', 'sortOrder' => 50],
                     ],
            ],
            'multi sorted array items' => [
                [
                    'key1' => ['value' => 'value 1'],
                    'key2' => ['value' => 'value 2'],
                    'item1' => [
                        'key3'=>['value' => 'value 3', 'sortOrder' => 30],
                        'key4'=>['value' => 'value 4', 'sortOrder' => 10],
                        'key5'=>['value' => 'value 5', 'sortOrder' => 20],
                    ],
                ],
                [
                    'key1' => ['value' => 'value 1'],
                    'key2' => ['value' => 'value 2'],
                    'item1' => [
                        'key4'=>['value' => 'value 4', 'sortOrder' => 10],
                        'key5'=>['value' => 'value 5', 'sortOrder' => 20],
                        'key3'=>['value' => 'value 3', 'sortOrder' => 30],
                    ],

                ],
            ],
            'pre-sorted array items' => [
                [
                    'item' => [
                        'key1' => ['value' => 'value 1'],
                        'key4' => ['value' => 'value 4'],
                        'key3' => ['value' => 'value 3'],
                        'key2' => ['value' => 'value 2', 'sortOrder' => 10],
                    ]
                ],
                [
                    'item' => [
                        'key1' => ['value' => 'value 1'],
                        'key4' => ['value' => 'value 4'],
                        'key3' => ['value' => 'value 3'],
                        'key2' => ['value' => 'value 2', 'sortOrder' => 10],
                    ]
                ],
            ],
            'sort order edge case values' => [
                [
                    'item' => [
                        'key1' => ['value' => 'value 1', 'sortOrder' => 101],
                        'key4' => ['value' => 'value 4'],
                        'key2' => ['value' => 'value 2', 'sortOrder' => -10],
                        'key3' => ['value' => 'value 3'],
                        'key5' => ['value' => 'value 5', 'sortOrder' => 20],
                    ],
                ],
                [
                    'item' => [
                        'key2' => ['value' => 'value 2', 'sortOrder' => -10],
                        'key4' => ['value' => 'value 4'],
                        'key3' => ['value' => 'value 3'],
                        'key5' => ['value' => 'value 5', 'sortOrder' => 20],
                        'key1' => ['value' => 'value 1', 'sortOrder' => 101],
                    ]
                ],
            ],
        ];
    }
}
