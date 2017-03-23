<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Converter;

use \Magento\Framework\Config\Converter\Dom;

class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $sourceFile
     * @param string $resultFile
     * @dataProvider convertDataProvider
     */
    public function testConvert($sourceFile, $resultFile)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents(__DIR__ . "/../_files/converter/dom/{$sourceFile}"));
        $resultFile = include __DIR__ . "/../_files/converter/dom/{$resultFile}";

        $converterDom = new Dom();
        $this->assertEquals($resultFile, $converterDom->convert($dom));
    }

    public function convertDataProvider()
    {
        return [
            ['cdata.xml', 'cdata.php'],
            ['attributes.xml', 'attributes.php',],
        ];
    }
}
