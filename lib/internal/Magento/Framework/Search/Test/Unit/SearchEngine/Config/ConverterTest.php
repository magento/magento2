<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\SearchEngine\Config;

use Magento\Framework\Search\SearchEngine\Config\Converter;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $converter = new Converter();
        $dom = new \DOMDocument();
        $dom->load(realpath(__DIR__ . '/../../_files/search_engine.xml'));
        $result = $converter->convert($dom);
        $expected = [
            'mysql' => ['synonyms'],
            'other' => ['synonyms', 'stopwords'],
            'none' => [],
        ];
        $this->assertEquals($expected, $result);
    }
}
