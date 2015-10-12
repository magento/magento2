<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\PreProcessor\Helper;

use Magento\Framework\View\Asset\PreProcessor\Helper\Sorter;

/**
 * Class SorterTest
 * @see \Magento\Framework\View\Asset\PreProcessor\Helper\Sorter
 */
class SorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $preprocessors
     * @param array $actual
     *
     * @dataProvider dataProviderTestSorting
     */
    public function testSorting(array $preprocessors, array $actual)
    {
        $sorter = new Sorter();

        $result = $sorter->sorting($preprocessors);

        static::assertEquals(array_keys($result), $actual);
    }

    /**
     * @return array
     */
    public function dataProviderTestSorting()
    {
        return [
            [
                'preprocessors' => [
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
                'actual' => [
                    'name-2', 'name-6', 'name-1', 'name-4', 'name-5', 'name-3'
                ]
            ],
            [
                'preprocessors' => [
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
                'actual' => [
                    'name-2', 'name-6', 'name-1', 'name-4', 'name-5', 'name-3'
                ]
            ],
            [
                'preprocessors' => [
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
                'actual' => [
                    'name-2', 'name-6', 'name-1', 'name-4', 'name-5', 'name-3', 'name-7', 'name-8'
                ]
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified does not exist preprocessor in the directive "after".
     */
    public function testSortingDirectiveException()
    {
        $preprocessors = [
            'name-1' => [ // 3
                'after' => 'name-6',
                'processor' => new \stdClass()
            ],
            'name-2' => [ // 1
                'processor' => new \stdClass()
            ],
            'name-3' => [ // 6
                'after' => 'name-XXX',
                'processor' => new \stdClass()
            ]
        ];

        $sorter = new Sorter();
        $sorter->sorting($preprocessors);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The sortable configuration will lead to infinite loop.
     */
    public function testSortingLoopingException()
    {
        $preprocessors = [
            'name-1' => [ // 3
                'after' => 'name-3',
                'processor' => new \stdClass()
            ],
            'name-2' => [ // 1
                'after' => 'name-1',
                'processor' => new \stdClass()
            ],
            'name-3' => [ // 6
                'after' => 'name-2',
                'processor' => new \stdClass()
            ]
        ];

        $sorter = new Sorter();
        $sorter->sorting($preprocessors);
    }
}
