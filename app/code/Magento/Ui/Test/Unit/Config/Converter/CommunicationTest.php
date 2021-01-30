<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\Communication;
use Magento\Ui\Config\ConverterUtils;
use PHPUnit\Framework\TestCase;

class CommunicationTest extends TestCase
{
    /**
     * @var Communication
     */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new Communication(new ConverterUtils());
    }

    public function testExportsConvert()
    {
        $expectedResult = [
            'name' => 'exports',
            'xsi:type' => 'array',
            'item' => [
                'propertyOne' => [
                    'name' => 'propertyOne',
                    'xsi:type' => 'string',
                    'value' => 'valueOne',
                ],
                'propertyTwo' => [
                    'name' => 'propertyTwo',
                    'xsi:type' => 'string',
                    'value' => 'valueTwo',
                ],
            ],
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'testForm.xml');
        $domXpath = new \DOMXPath($dom);
        $classes = $domXpath->query('//form/fieldset/settings/exports')->item(0);
        $this->assertEquals($expectedResult, $this->converter->convert($classes));
    }

    public function testImportsConvert()
    {
        $expectedResult = [
            'name' => 'imports',
            'xsi:type' => 'array',
            'item' => [
                'propertyOne' => [
                    'name' => 'propertyOne',
                    'xsi:type' => 'string',
                    'value' => 'valueOne',
                ],
                'propertyTwo' => [
                    'name' => 'propertyTwo',
                    'xsi:type' => 'string',
                    'value' => 'valueTwo',
                ],
            ],
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'testForm.xml');
        $domXpath = new \DOMXPath($dom);
        $classes = $domXpath->query('//form/fieldset/settings/imports')->item(0);
        $this->assertEquals($expectedResult, $this->converter->convert($classes));
    }

    public function testListensConvert()
    {
        $expectedResult = [
            'name' => 'listens',
            'xsi:type' => 'array',
            'item' => [
                'propertyOne' => [
                    'name' => 'propertyOne',
                    'xsi:type' => 'string',
                    'value' => 'valueOne',
                ],
                'propertyTwo' => [
                    'name' => 'propertyTwo',
                    'xsi:type' => 'string',
                    'value' => 'valueTwo',
                ],
            ],
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'testForm.xml');
        $domXpath = new \DOMXPath($dom);
        $classes = $domXpath->query('//form/fieldset/settings/listens')->item(0);
        $this->assertEquals($expectedResult, $this->converter->convert($classes));
    }

    public function testLinksConvert()
    {
        $expectedResult = [
            'name' => 'links',
            'xsi:type' => 'array',
            'item' => [
                'propertyOne' => [
                    'name' => 'propertyOne',
                    'xsi:type' => 'string',
                    'value' => 'valueOne',
                ],
                'propertyTwo' => [
                    'name' => 'propertyTwo',
                    'xsi:type' => 'string',
                    'value' => 'valueTwo',
                ],
            ],
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'testForm.xml');
        $domXpath = new \DOMXPath($dom);
        $classes = $domXpath->query('//form/fieldset/settings/links')->item(0);
        $this->assertEquals($expectedResult, $this->converter->convert($classes));
    }
}
