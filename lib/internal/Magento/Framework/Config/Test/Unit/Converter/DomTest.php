<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Converter;

use Magento\Framework\Config\Converter\Dom;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DomTest extends TestCase
{
    /**
     * @param string $sourceFile
     * @param string $resultFile     */
    #[DataProvider('convertDataProvider')]
    public function testConvert($sourceFile, $resultFile)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents(__DIR__ . "/../_files/converter/dom/{$sourceFile}"));
        $resultFile = include __DIR__ . "/../_files/converter/dom/{$resultFile}";

        $converterDom = new Dom();
        $this->assertEquals($resultFile, $converterDom->convert($dom));
    }

    /**
     * @return array
     */
    public static function convertDataProvider()
    {
        return [
            ['cdata.xml', 'cdata.php'],
            ['attributes.xml', 'attributes.php'],
        ];
    }
}
