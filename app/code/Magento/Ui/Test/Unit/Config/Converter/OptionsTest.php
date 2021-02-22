<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\Options;
use Magento\Ui\Config\ConverterUtils;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Options
     */
    private $converter;

    /**
     * @var \DOMXPath
     */
    private $domXpath;

    protected function setUp(): void
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files/test.xml');
        $this->domXpath = new \DOMXPath($dom);
        $this->converter = new Options(new ConverterUtils());
    }

    /**
     * @param array $expectedResult
     * @param string $xpath
     * @return void
     * @dataProvider convertDataProvider
     */
    public function testConvert(array $expectedResult, $xpath)
    {
        $node = $this->domXpath->query($xpath)->item(0);
        $res = $this->converter->convert($node);
        $this->assertEquals($expectedResult, $res);
    }

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        return [
            [
                [
                    'name' => 'options',
                    'xsi:type' => 'array',
                    'item' => [
                        'anySimpleType' => [
                            'xsi:type' => 'boolean',
                            'name' => 'anySimpleType',
                            'value' => 'true',
                        ],
                    ],
                ],
                '//listing/columns/column/settings/options[1]'
            ],
            [
                [
                    'value' => 'Magento\Test\OptionsProvider',
                    'name' => 'options',
                    'xsi:type' => 'object'
                ],
                '//listing/columns/column/settings/options[2]'
            ],
        ];
    }
}
