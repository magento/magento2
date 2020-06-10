<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\SearchEngine\Config;

use Magento\Framework\Search\SearchEngine\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
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
