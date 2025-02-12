<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\Actions;
use Magento\Ui\Config\ConverterInterface;
use Magento\Ui\Config\ConverterUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionsTest extends TestCase
{
    /**
     * @var Actions
     */
    private $converter;

    /**
     * @var ConverterInterface|MockObject
     */
    private $urlConverter;

    protected function setUp(): void
    {
        $this->urlConverter = $this->getMockBuilder(ConverterInterface::class)
            ->getMockForAbstractClass();
        $this->converter = new Actions($this->urlConverter, new ConverterUtils());
    }

    /**
     * @param array $expectedResult
     * @param string $xpath
     * @dataProvider convertDataProvider
     */
    public function testConvert(array $expectedResult, $xpath)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test.xml');
        $domXpath = new \DOMXPath($dom);
        $actions = $domXpath->query($xpath)->item(0);
        $urlResult = [
            'name' => 'url',
            'xsi:type' => 'url',
            'path' => 'url',
            'param' => [
                'first' => [
                    'name' => 'first',
                    'value' => 'first_value'
                ],
                'second' => [
                    'name' => 'second',
                    'value' => 'second_value'
                ],

            ],
        ];
        $this->urlConverter->expects($this->any())
            ->method('convert')
            ->willReturn($urlResult);
        $this->assertEquals($expectedResult, $this->converter->convert($actions));
    }

    /**
     * @return array
     */
    public static function convertDataProvider()
    {
        return [
            [
                [
                    'name' => 'actions',
                    'xsi:type' => 'array',
                    'item' => [
                        'action_one' => [
                            'name' => 'action_one',
                            'xsi:type' => 'array',
                            'item' => [
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'translate' => 'true',
                                    'value' => 'Label Actions One',
                                ],
                                'custom_param_one' => [
                                    'name' => 'custom_param_one',
                                    'xsi:type' => 'string',
                                    'value' => 'custom_value_one'
                                ],
                                'type' => [
                                    'name' => 'type',
                                    'xsi:type' => 'string',
                                    'value' => 'action_one_type',
                                ],
                                'url' => [
                                    'name' => 'url',
                                    'xsi:type' => 'url',
                                    'path' => 'url',
                                    'param' => [
                                        'first' => [
                                            'name' => 'first',
                                            'value' => 'first_value'
                                        ],
                                        'second' => [
                                            'name' => 'second',
                                            'value' => 'second_value'
                                        ],

                                    ],
                                ],
                            ],
                        ],
                        'action_two' => [
                            'name' => 'action_two',
                            'xsi:type' => 'array',
                            'item' => [
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'translate' => 'true',
                                    'value' => 'Label Actions Two',
                                ],
                                'custom_param_two' => [
                                    'name' => 'custom_param_two',
                                    'xsi:type' => 'string',
                                    'value' => 'custom_value_two'
                                ],
                                'type' => [
                                    'name' => 'type',
                                    'xsi:type' => 'string',
                                    'value' => 'action_two_type',
                                ],
                                'url' => [
                                    'name' => 'url',
                                    'xsi:type' => 'url',
                                    'path' => 'url',
                                    'param' => [
                                        'first' => [
                                            'name' => 'first',
                                            'value' => 'first_value'
                                        ],
                                        'second' => [
                                            'name' => 'second',
                                            'value' => 'second_value'
                                        ],

                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '//listing/listingToolbar/massaction[@name="listing_massaction"]/settings/actions'
            ],
            [
                [
                    'name' => 'actions',
                    'xsi:type' => 'object',
                    'value' => 'Some_Actions_Class'
                ],
                '//listing/listingToolbar/massaction[@name="listing_massaction"]/action/settings/actions'
            ]
        ];
    }
}
