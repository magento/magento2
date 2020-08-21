<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Test\Unit\AclResource\Config\Converter;

use Magento\Framework\Acl\AclResource\Config\Converter\Dom;
use PHPUnit\Framework\TestCase;

class DomTest extends TestCase
{
    /**
     * @var Dom
     */
    protected $_converter;

    protected function setUp(): void
    {
        $this->_converter = new Dom();
    }

    /**
     * @param array $expectedResult
     * @param string $xml
     * @dataProvider convertWithValidDomDataProvider
     */
    public function testConvertWithValidDom(array $expectedResult, $xml)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $this->assertEquals($expectedResult, $this->_converter->convert($dom));
    }

    /**
     * @return array
     */
    public function convertWithValidDomDataProvider()
    {
        return [
            [
                include __DIR__ . '/_files/converted_valid_acl.php',
                file_get_contents(__DIR__ . '/_files/valid_acl.xml'),
            ]
        ];
    }

    /**
     * @param string $xml
     * @dataProvider convertWithInvalidDomDataProvider
     */
    public function testConvertWithInvalidDom($xml)
    {
        $this->expectException('Exception');
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $this->_converter->convert($dom);
    }

    /**
     * @return array
     */
    public function convertWithInvalidDomDataProvider()
    {
        return [
            [
                'resource without "id" attribute' => '<?xml version="1.0"?><config><acl>' .
                '<resources><resource/></resources></acl></config>',
            ]
        ];
    }
}
