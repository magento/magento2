<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Config\Mapper;

use Magento\Framework\ObjectManager\Config\Mapper\ArgumentParser;
use PHPUnit\Framework\TestCase;

class ArgumentParserTest extends TestCase
{
    public function testParse()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/_files/argument_parser.xml');
        $parser = new ArgumentParser();
        $actual = $parser->parse($document->getElementsByTagName('argument')->item(0));
        $expected = [
            'item' => [
                'one' => ['name' => 'one', 'value' => 'value1'],
                'nested' => [
                    'name' => 'nested',
                    'item' => [
                        'two' => ['name' => 'two', 'value' => 'value2'],
                        'three' => ['name' => 'three', 'value' => 'value3'],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $actual);
    }
}
