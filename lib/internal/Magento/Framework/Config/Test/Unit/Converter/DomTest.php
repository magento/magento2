<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Converter;

use Magento\Framework\Config\Converter\Dom;
use PHPUnit\Framework\TestCase;

class DomTest extends TestCase
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

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        return [
            ['cdata.xml', 'cdata.php'],
            ['attributes.xml', 'attributes.php'],
        ];
    }
}
