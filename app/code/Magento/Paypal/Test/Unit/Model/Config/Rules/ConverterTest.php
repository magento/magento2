<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

use Magento\Paypal\Model\Config\Rules\Converter;
use PHPUnit\Framework\TestCase;

/**
 * Class ConverterTest
 *
 * Test for class \Magento\Paypal\Model\Config\Rules\Converter
 */
class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->converter = new Converter();
    }

    /**
     * Run test for convert method
     *
     * @param array $expected
     *
     * @dataProvider dataProviderExpectedData
     */
    public function testConvert(array $expected)
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/ConvertibleContent/rules.xml');

        $this->assertEquals($expected, $this->converter->convert($document));
    }

    /**
     * Data provider for testConvert
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function dataProviderExpectedData()
    {
        return [
            [
                'expected' => [
                    'payment_1' => [
                        'events' => [
                            'test-selector' => [
                                'event0' => [
                                    'value' => '0',
                                    'predicate' => [
                                    ],
                                    'include' => '',
                                ],
                                'event1' => [
                                    'value' => '1',
                                    'predicate' => [
                                        'name' => 'predicate1',
                                        'message' => 'Test message!',
                                        'event' => 'event1',
                                        'argument' => [
                                            'argument1' => 'argument1',
                                            'argument2' => 'argument2',
                                        ],
                                    ],
                                    'include' => '',
                                ],
                            ],
                        ],
                        'relations' => [
                            'payment_test_1' => [
                                'test' => [
                                    [
                                        'event' => 'event0',
                                        'argument' => [],
                                    ]
                                ],
                            ],
                            'payment_test_2' => [
                                'test' => [
                                    [
                                        'event' => 'event1',
                                        'argument' => [],
                                    ]
                                ],
                                'test-two' => [
                                    [
                                        'event' => 'event1',
                                        'argument' => [
                                            'argument1' => 'argument1',
                                            'argument2' => 'argument2',
                                        ],
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'payment_2' => [
                        'events' => [
                            'test-selector' => [
                                'event0' => [
                                    'value' => '0',
                                    'predicate' => [],
                                    'include' => '',
                                ],
                                'event1' => [
                                    'value' => '1',
                                    'predicate' => [
                                        'name' => 'predicate1',
                                        'message' => 'Test message!',
                                        'event' => 'event1',
                                        'argument' => [
                                            'argument1' => 'argument1',
                                            'argument2' => 'argument2',
                                        ],
                                    ],
                                    'include' => '',
                                ],
                            ],
                        ],
                        'relations' => [
                            'payment_test_1' => [
                                'test' => [
                                    [
                                        'event' => 'event0',
                                        'argument' => [],
                                    ]
                                ],
                            ],
                            'payment_test_2' => [
                                'test' => [
                                    [
                                        'event' => 'event1',
                                        'argument' => [],
                                    ]
                                ],
                                'test-two' => [
                                    [
                                        'event' => 'event1',
                                        'argument' => [
                                            'argument1' => 'argument1',
                                            'argument2' => 'argument2',
                                        ],
                                    ],
                                    [
                                        'event' => 'event2',
                                        'argument' => [
                                            'argument1' => 'argument1',
                                            'argument2' => 'argument2',
                                        ],
                                    ]
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }
}
