<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Test\Unit;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;

/**
 * Test for ArrayUtils.
 *
 * @see ArrayUtils
 */
class ArrayUtilsTest extends TestCase
{
    /**
     * @var ArrayUtils
     */
    protected $_arrayUtils;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_arrayUtils = new ArrayUtils();
    }

    /**
     * Tests ksort multibyte.
     *
     * @param array $input
     * @param string $locale
     * @dataProvider ksortMultibyteDataProvider
     */
    public function testKsortMultibyte($input, $locale)
    {
        $this->_arrayUtils->ksortMultibyte($input, $locale);

        $iterator = 0;
        foreach ($input as $value) {
            $iterator++;
            $this->assertEquals($iterator, $value);
        }
    }

    /**
     * Data provider for ksortMultibyteDataProvider
     * @todo implement provider with values which different depends on locale
     */
    public static function ksortMultibyteDataProvider()
    {
        return [[['б' => 2, 'в' => 3, 'а' => 1], 'ru_RU']];
    }

    public function testDecorateArray()
    {
        $original = [['value' => 1], ['value' => 2], ['value' => 3]];
        $decorated = [
            ['value' => 1, 'is_first' => true, 'is_odd' => true],
            ['value' => 2, 'is_even' => true],
            ['value' => 3, 'is_last' => true, 'is_odd' => true],
        ];

        // arrays
        $this->assertEquals($decorated, $this->_arrayUtils->decorateArray($original, ''));

        // \Magento\Framework\DataObject
        $sample = [
            new DataObject($original[0]),
            new DataObject($original[1]),
            new DataObject($original[2]),
        ];
        $decoratedVo = [
            new DataObject($decorated[0]),
            new DataObject($decorated[1]),
            new DataObject($decorated[2]),
        ];
        $this->assertEquals($decoratedVo, $this->_arrayUtils->decorateArray($sample, ''));
    }

    /**
     * Test flattening of array.
     *
     * @param array $data
     * @param array $expected
     * @param string $path
     * @param string $separator
     * @dataProvider flattenDataProvider
     */
    public function testFlatten(array $data, array $expected, $path, $separator)
    {
        $this->assertSame($expected, $this->_arrayUtils->flatten($data, $path, $separator));
    }

    /**
     * @return array
     */
    public static function flattenDataProvider()
    {
        return [
            [
                [
                    'default' => ['web' => ['unsecure' => ['base_url' => 'http://magento2.local/']]],
                    'websites' => ['base' => ['web' => ['unsecure' => ['base_url' => 'http://magento2.local/']]]],
                ],
                [
                    'default/web/unsecure/base_url' => 'http://magento2.local/',
                    'websites/base/web/unsecure/base_url' => 'http://magento2.local/',
                ],
                '',
                '/'
            ],
            [
                [
                    'default' => ['web' => ['unsecure' => ['base_url' => 'http://magento2.local/']]],
                ],
                [
                    'default+web+unsecure+base_url' => 'http://magento2.local/',
                ],
                '',
                '+',
            ],
            [
                [
                    'default' => ['web' => ['unsecure' => ['base_url' => 'http://magento2.local/']]],
                ],
                [
                    'test+default+web+unsecure+base_url' => 'http://magento2.local/',
                ],
                'test',
                '+',
            ],
            [
                [
                    'default' => ['unsecure' => 'http://magento2.local/'],
                ],
                [
                    'test/default/unsecure' => 'http://magento2.local/',
                ],
                'test',
                '/',
            ],
            [
                [
                    'unsecure' => 'http://magento2.local/',
                ],
                [
                    'unsecure' => 'http://magento2.local/',
                ],
                '',
                '/',
            ],
            [
                [],
                [],
                '',
                '/',
            ]
        ];
    }

    /**
     * Tests recursive diff between arrays.
     *
     * @param array $originalArray
     * @param array $newArray
     * @param $expected
     * @dataProvider recursiveDiffDataProvider
     */
    public function testRecursiveDiff(array $originalArray, array $newArray, $expected)
    {
        $this->assertSame($expected, $this->_arrayUtils->recursiveDiff($originalArray, $newArray));
    }

    /**
     * @return array
     */
    public static function recursiveDiffDataProvider()
    {
        return [
            [
                [
                    'test' => ['test2' => 2]
                ],
                [],
                [
                    'test' => ['test2' => 2]
                ]
            ],
            [
                [
                    'test' => ['test2' => 2]
                ],
                [
                    'test' => ['test2' => 2]
                ],
                []
            ],
            [
                [
                    'test' => ['test2' => ['test3' => 3, 'test4' => 4]]
                ],
                [
                    'test' => ['test3' => 3]
                ],
                [
                    'test' => ['test2' => ['test3' => 3, 'test4' => 4]]
                ]
            ]
        ];
    }
}
