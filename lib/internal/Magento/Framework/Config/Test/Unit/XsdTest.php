<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $xsdFile
     * @param string $invalidXmlFile
     * @param int $expectedErrorsQty
     * @dataProvider invalidXmlFileDataProvider
     */
    public function testInvalidXmlFile($xsdFile, $invalidXmlFile, $expectedErrorsQty)
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$invalidXmlFile}");
        $schema = __DIR__ . "/../../etc/{$xsdFile}";

        libxml_use_internal_errors(true);
        $result = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schema);
        $errorsQty = count($result);
        libxml_use_internal_errors(false);

        if ($expectedErrorsQty > 0) {
            $this->assertNotEmpty($result);
        }
        $this->assertEquals($expectedErrorsQty, $errorsQty);
    }

    /**
     * @return array
     */
    public function invalidXmlFileDataProvider()
    {
        return [['view.xsd', 'view_invalid.xml', 8], ['theme.xsd', 'theme_invalid.xml', 1]];
    }
}
