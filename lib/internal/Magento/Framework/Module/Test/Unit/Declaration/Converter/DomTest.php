<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit\Declaration\Converter;

class DomTest extends \PHPUnit\Framework\TestCase
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
     * @dataProvider convertWithInvalidDomDataProvider
     * @expectedException \Exception
     */
    public function testConvertWithInvalidDom($xmlString)
    {
        $dom = new \DOMDocument();
        try {
            $dom->loadXML($xmlString);
            $this->_converter->convert($dom);
        } catch (\PHPUnit\Framework\Error $ex) {
            // do nothing because we expect \Exception but not \PHPUnit\Framework\Error
        }
    }

    /**
     * @return array
     */
    public function convertWithInvalidDomDataProvider()
    {
        return [
            'Module node without "name" attribute' => ['<?xml version="1.0"?><config><module /></config>'],
            'Sequence module node without "name" attribute' => [
                '<?xml version="1.0"?><config><module name="Module_One" setup_version="1.0.0.0">' .
                '<sequence><module/></sequence></module></config>',
            ],
        ];
    }
}
