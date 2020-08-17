<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset\PreProcessor\Helper;

use Magento\Framework\View\Asset\PreProcessor\Helper\Sort;
use PHPUnit\Framework\TestCase;

/**
 *
 * @see \Magento\Framework\View\Asset\PreProcessor\Helper\Sorter2
 */
class SortTest extends TestCase
{
    /**
     * @param array $arrayData
     * @param array $expected
     *
     * @dataProvider dataProviderTestSorting
     */
    public function testSorting(array $arrayData, array $expected, $message)
    {
        $sorter = new Sort();

        $result = $sorter->sort($arrayData);

        static::assertEquals($expected, array_keys($result), $message);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderTestSorting()
    {
        return [
            [
                'arrayData' => [
                    'name-1' => [ // 2
                        'after' => 'name-3',
                        'processor' => new \stdClass()
                    ],
                    'name-2' => [ // 0
                        'processor' => new \stdClass()
                    ],
                    'name-3' => [ // 1
                        'after' => 'name-2',
                        'processor' => new \stdClass()
                    ],
                ],
                'expected' => [
                    'name-2', 'name-3', 'name-1'
                ],
                'message' => 'variation-1',
            ],
            [
                'arrayData' => [
                    'name-1' => [ // 3
                        'after' => 'name-6',
                        'processor' => new \stdClass()
                    ],
                    'name-2' => [ // 1
                        'processor' => new \stdClass()
                    ],
                    'name-3' => [ // 6
                        'after' => 'name-5',
                        'processor' => new \stdClass()
                    ],
                    'name-4' => [ // 4
                        'after' => 'name-1',
                        'processor' => new \stdClass()
                    ],
                    'name-5' => [ // 5
                        'after' => 'name-4',
                        'processor' => new \stdClass()
                    ],
                    'name-6' => [ // 2
                        'after' => 'name-2',
                        'processor' => new \stdClass()
                    ],
                ],
                'expected' => [
                    'name-2', 'name-6', 'name-1', 'name-4', 'name-5', 'name-3'
                ],
                'message' => 'variation-2',
            ],
            [
                'arrayData' => [
                    'name-1' => [ // 3
                        'after' => 'name-6',
                        'processor' => new \stdClass()
                    ],
                    'name-3' => [ // 6
                        'after' => 'name-5',
                        'processor' => new \stdClass()
                    ],
                    'name-4' => [ // 4
                        'after' => 'name-1',
                        'processor' => new \stdClass()
                    ],
                    'name-5' => [ // 5
                        'after' => 'name-4',
                        'processor' => new \stdClass()
                    ],
                    'name-6' => [ // 2
                        'after' => 'name-2',
                        'processor' => new \stdClass()
                    ],
                    'name-2' => [ // 1
                        'processor' => new \stdClass()
                    ],
                ],
                'expected' => [
                    'name-2', 'name-6', 'name-1', 'name-4', 'name-5', 'name-3'
                ],
                'message' => 'variation-3',
            ],
            [
                'arrayData' => [
                    'name-1' => [ // 3
                        'after' => 'name-6',
                        'processor' => new \stdClass()
                    ],
                    'name-2' => [ // 1
                        'processor' => new \stdClass()
                    ],
                    'name-3' => [ // 6
                        'after' => 'name-5',
                        'processor' => new \stdClass()
                    ],
                    'name-4' => [ // 4
                        'after' => 'name-1',
                        'processor' => new \stdClass()
                    ],
                    'name-5' => [ // 5
                        'after' => 'name-4',
                        'processor' => new \stdClass()
                    ],
                    'name-6' => [ // 2
                        'after' => 'name-2',
                        'processor' => new \stdClass()
                    ],
                    'name-7' => [ // end
                        'processor' => new \stdClass()
                    ],
                    'name-8' => [ // end
                        'processor' => new \stdClass()
                    ],
                ],
                'expected' => [
                    'name-2', 'name-6', 'name-1', 'name-4', 'name-5', 'name-3', 'name-7', 'name-8'
                ],
                'message' => 'variation-4',
            ],
            [
                'arrayData' => [
                    'name-1' => [ // xxx
                        'after' => 'name-6',
                        'processor' => new \stdClass()
                    ],
                    'name-2' => [ // 1
                        'processor' => new \stdClass()
                    ],
                    'name-3' => [ // xxx
                        'after' => 'name-XXX',
                        'processor' => new \stdClass()
                    ]
                ],
                'expected' => ['name-2'],
                'message' => 'variation-5',
            ],
            [
                'arrayData' => [
                    'name-1' => [ // xxx
                        'after' => 'name-3',
                        'processor' => new \stdClass()
                    ],
                    'name-2' => [ // xxx
                        'after' => 'name-1',
                        'processor' => new \stdClass()
                    ],
                    'name-3' => [ // xxx
                        'after' => 'name-2',
                        'processor' => new \stdClass()
                    ]
                ],
                'expected' => [],
                'message' => 'variation-6',
            ],
        ];
    }
}
