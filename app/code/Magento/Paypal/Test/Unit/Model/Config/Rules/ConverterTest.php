<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

/**
 * Class ConverterTest
 *
 * Test for class \Magento\Paypal\Model\Config\Rules\Converter
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Config\Rules\Converter
     */
    protected $converter;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->converter = new \Magento\Paypal\Model\Config\Rules\Converter();
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
    public function dataProviderExpectedData()
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
