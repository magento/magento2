<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Test sanitizer for different kind of scenarios.
 */
class SanitizerTest extends TestCase
{
    /**
     * @var Sanitizer
     */
    private $sanitizer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->sanitizer = new Sanitizer();
    }

    /**
     * Data sets to sanitize.
     *
     * @return array
     */
    public function getSanitizeDataSets(): array
    {
        return [
            'simpleSet' => [
                ['foo' => '${\'bar\'}', 'xyz' => 42],
                ['foo' => '${\'bar\'}', 'xyz' => 42, '__disableTmpl' => ['foo' => true]]
            ],
            'configuredSet' => [
                ['foo' => 'bar', 'xyz' => '${\'zyx\'}', '__disableTmpl' => true],
                ['foo' => 'bar', 'xyz' => '${\'zyx\'}', '__disableTmpl' => true]
            ],
            'partiallyConfiguredSet' => [
                ['foo' => '${\'bar\'}', 'xyz' => '${\'zyx\'}', '__disableTmpl' => ['foo' => false]],
                ['foo' => '${\'bar\'}', 'xyz' => '${\'zyx\'}', '__disableTmpl' => ['foo' => false, 'xyz' => true]]
            ],
            'enabledSet' => [
                ['foo' => 'bar', 'xyz' => '${\'zyx\'}', '__disableTmpl' => false],
                ['foo' => 'bar', 'xyz' => '${\'zyx\'}', '__disableTmpl' => false]
            ],
            'complexSet' => [
                [
                    'foo' => 'bar',
                    'sub1' => ['foo' => '${\'bar\'}'],
                    'sub2' => [
                        'field' => '${\'value\'}',
                        'subSub1' => ['foo' => 'bar'],
                        'subSub2' => ['foo' => '${\'bar\'}', '__disableTmpl' => false],
                        'subSub3' => [
                            'fooSub' => [
                                'foo' => '${\'bar\'}',
                                '__disableTmpl' => false,
                                'subSubSub1' => ['field' => '${\'value\'}']
                            ]
                        ],
                        'subSub4' => [['foo' => '${\'bar\'}'], ['foo' => '${\'bar\'}', 'xyz' => '${\'zyx\'}']]
                    ]
                ],
                [
                    'foo' => 'bar',
                    'sub1' => ['foo' => '${\'bar\'}', '__disableTmpl' => ['foo' => true]],
                    'sub2' => [
                        'field' => '${\'value\'}',
                        'subSub1' => ['foo' => 'bar'],
                        'subSub2' => ['foo' => '${\'bar\'}', '__disableTmpl' => false],
                        'subSub3' => [
                            'fooSub' => [
                                'foo' => '${\'bar\'}',
                                '__disableTmpl' => false,
                                'subSubSub1' => ['field' => '${\'value\'}', '__disableTmpl' => ['field' => true]]
                            ]
                        ],
                        'subSub4' => [
                            ['foo' => '${\'bar\'}', '__disableTmpl' => ['foo' => true]],
                            [
                                'foo' => '${\'bar\'}',
                                'xyz' => '${\'zyx\'}',
                                '__disableTmpl' => ['foo' => true, 'xyz' => true]
                            ]
                        ],
                        '__disableTmpl' => ['field' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Test sanitize method for different data sets.
     *
     * @param array $input
     * @param array $expectedOutput
     * @return void
     * @dataProvider getSanitizeDataSets
     */
    public function testSanitize(array $input, array $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, $this->sanitizer->sanitize($input));
    }

    /**
     * Full UI component data sets to sanitize.
     *
     * @return array
     */
    public function getSanitizeComponentDataSets(): array
    {
        return [
            'simpleComponent' => [
                [
                    'arguments' => ['data' => ['config' => ['foo' => '${\'bar\'}', 'xyz' => 42]]],
                    'children' => [
                        'child_component' => [
                            'arguments' => ['data' => ['config' => ['foo' => '${\'bar\'}', 'xyz' => '${\'xyz\'}']]]
                        ]
                    ]
                ],
                [
                    'arguments' => [
                        'data' => [
                            'config' => ['foo' => '${\'bar\'}', 'xyz' => 42,  '__disableTmpl' => ['foo' => true]]
                        ]
                    ],
                    'children' => [
                        'child_component' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'foo' => '${\'bar\'}',
                                        'xyz' => '${\'xyz\'}',
                                        '__disableTmpl' => ['foo' => true, 'xyz' => true]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'argumentsOnly' => [
                ['arguments' => ['data' => ['config' => ['foo' => '${\'bar\'}']]]],
                ['arguments' => ['data' => ['config' => ['foo' => '${\'bar\'}', '__disableTmpl' => ['foo' => true]]]]]
            ],
            'childrenOnly' => [
                ['children' => ['child1' => ['arguments' => ['data' => ['config' => ['foo' => '${\'bar\'}']]]]]],
                [
                    'children' => [
                        'child1' => [
                            'arguments' => [
                                'data' => ['config' => ['foo' => '${\'bar\'}', '__disableTmpl' => ['foo' => true]]]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Test sanitizeComponentMetadata method for different data sets.
     *
     * @param array $input
     * @param array $expectedOutput
     * @return void
     * @dataProvider getSanitizeComponentDataSets
     */
    public function testSanitizeComponentMetadata(array $input, array $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, $this->sanitizer->sanitizeComponentMetadata($input));
    }
}
