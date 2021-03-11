<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Framework\Data\Argument\Interpreter\Composite;
use Magento\Ui\Config\Converter\Deps;
use Magento\Ui\Config\ConverterUtils;

class DepsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Deps
     */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new Deps(new ConverterUtils());
    }

    public function testConvert()
    {
        $expectedResult = [
            'name' => 'deps',
            'xsi:type' => 'array',
            'item' => [
                [
                    'name' => 0,
                    'xsi:type' => 'string',
                    'value' => 'test-dep',
                ],
                [
                    'name' => 1,
                    'xsi:type' => 'string',
                    'value' => 'test-dep-two',
                ],
            ],
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test.xml');
        $domXpath = new \DOMXPath($dom);
        $deps = $domXpath->query('//listing/settings/deps')->item(0);
        $this->assertEquals($expectedResult, $this->converter->convert($deps));
    }
}
