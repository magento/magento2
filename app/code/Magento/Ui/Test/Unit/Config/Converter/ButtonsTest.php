<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\Buttons;
use Magento\Ui\Config\ConverterInterface;
use Magento\Ui\Config\ConverterUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ButtonsTest extends TestCase
{
    /**
     * @var Buttons
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
        $this->converter = new Buttons($this->urlConverter, new ConverterUtils());
    }

    public function testConvert()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test.xml');
        $domXpath = new \DOMXPath($dom);
        $buttons = $domXpath->query('//listing/settings/buttons')->item(0);
        $url = $domXpath->query('//listing/settings/buttons/button[@name="button_2"]/url')->item(0);
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
            ->with($url, ['type' => 'url'])
            ->willReturn($urlResult);
        $expectedResult = [
            'name' => 'buttons',
            'xsi:type' => 'array',
            'item' => [
                'button_1' => [
                    'name' => 'button_1',
                    'xsi:type' => 'string',
                    'value' => 'Some_Class',
                ],
                'button_2' => [
                    'name' => 'button_2',
                    'xsi:type' => 'array',
                    'item' => [
                        'class' => [
                            'name' => 'class',
                            'xsi:type' => 'string',
                            'value' => 'css_class',
                        ],
                        'label' => [
                            'name' => 'label',
                            'xsi:type' => 'string',
                            'translate' => 'true',
                            'value' => 'Label Button 2',
                        ],
                        'url' => $urlResult,
                        'custom_param' => [
                            'name' => 'custom_param',
                            'xsi:type' => 'string',
                            'value' => 'custom_value'
                        ],
                        'name' => [
                            'name' => 'name',
                            'xsi:type' => 'string',
                            'value' => 'button_2',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedResult, $this->converter->convert($buttons));
    }

    public function testConvertEmptyButtons()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'testForm.xml');
        $domXpath = new \DOMXPath($dom);
        $buttons = $domXpath->query('//form/settings/buttons')->item(0);
        $expectedResult = [
            'name' => 'buttons',
            'xsi:type' => 'array',
            'item' => []
        ];

        $this->assertEquals($expectedResult, $this->converter->convert($buttons));
    }
}
