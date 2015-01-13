<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Declaration\Converter;

class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Declaration\Converter\Dom
     */
    protected $_converter;

    protected function setUp()
    {
        $this->_converter = new \Magento\Framework\Module\Declaration\Converter\Dom();
    }

    public function testConvertWithValidDom()
    {
        $xmlFilePath = __DIR__ . '/_files/valid_module.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFilePath));
        $expectedResult = include __DIR__ . '/_files/converted_valid_module.php';
        $this->assertEquals($expectedResult, $this->_converter->convert($dom));
    }

    /**
     * @param string $xmlString
     * @dataProvider testConvertWithInvalidDomDataProvider
     * @expectedException \Exception
     */
    public function testConvertWithInvalidDom($xmlString)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlString);
        $this->_converter->convert($dom);
    }

    public function testConvertWithInvalidDomDataProvider()
    {
        return [
            'Module node without "name" attribute' => ['<?xml version="1.0"?><config><module /></config>'],
            'Sequence module node without "name" attribute' => [
                '<?xml dbversion="1.0"?><config><module name="Module_One" schema_version="1.0.0.0">' .
                '<sequence><module/></sequence></module></config>',
            ],
        ];
    }
}
