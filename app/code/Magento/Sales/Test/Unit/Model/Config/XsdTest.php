<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_xsdFile;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_xsdFile = "urn:magento:module:Magento_Sales:etc/sales.xsd";
    }

    /**
     * @param string $xmlFile
     * @dataProvider validXmlFileDataProvider
     */
    public function testValidXmlFile($xmlFile)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$xmlFile}");
        libxml_use_internal_errors(true);
        $result = \Magento\Framework\Config\Dom::validateDomDocument($dom, $this->_xsdFile);
        libxml_use_internal_errors(false);
        $this->assertEmpty($result, 'Validation failed with errors: ' . join(', ', $result));
    }

    /**
     * @return array
     */
    public function validXmlFileDataProvider()
    {
        return [['sales_valid.xml']];
    }

    /**
     * @param string $xmlFile
     * @param array $expectedErrors
     * @dataProvider invalidXmlFileDataProvider
     */
    public function testInvalidXmlFile($xmlFile, $expectedErrors)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$xmlFile}");
        libxml_use_internal_errors(true);

        $result = \Magento\Framework\Config\Dom::validateDomDocument($dom, $this->_xsdFile);

        libxml_use_internal_errors(false);
        $this->assertEquals($expectedErrors, $result);
    }

    /**
     * @return array
     */
    public function invalidXmlFileDataProvider()
    {
        return [
            [
                'sales_invalid.xml',
                [
                    "Element 'section', attribute 'wrongName': The attribute 'wrongName' is not allowed.\nLine: 9\n",
                    "Element 'section': The attribute 'name' is required but missing.\nLine: 9\n",
                    "Element 'wrongGroup': This element is not expected. Expected is ( group ).\nLine: 10\n"
                ],
            ],
            [
                'sales_invalid_duplicates.xml',
                [
                    "Element 'renderer': Duplicate key-sequence ['r1']" .
                        " in unique identity-constraint 'uniqueRendererName'.\nLine: 13\n",
                    "Element 'item': Duplicate key-sequence ['i1']" .
                        " in unique identity-constraint 'uniqueItemName'.\nLine: 15\n",
                    "Element 'group': Duplicate key-sequence ['g1']" .
                        " in unique identity-constraint 'uniqueGroupName'.\nLine: 17\n",
                    "Element 'section': Duplicate key-sequence ['s1']" .
                        " in unique identity-constraint 'uniqueSectionName'.\nLine: 21\n",
                    "Element 'available_product_type': Duplicate key-sequence ['a1']" .
                        " in unique identity-constraint 'uniqueProductTypeName'.\nLine: 28\n"
                ]
            ],
            [
                'sales_invalid_without_attributes.xml',
                [
                    "Element 'section': The attribute 'name' is required but missing.\nLine: 9\n",
                    "Element 'group': The attribute 'name' is required but missing.\nLine: 10\n",
                    "Element 'item': The attribute 'name' is required but missing.\nLine: 11\n",
                    "Element 'renderer': The attribute 'name' is required but missing.\nLine: 12\n",
                    "Element 'renderer': The attribute 'instance' is required but missing.\nLine: 12\n",
                    "Element 'available_product_type': The attribute 'name' is required but missing.\nLine: 17\n"
                ]
            ],
            [
                'sales_invalid_root_node.xml',
                ["Element 'wrong': This element is not expected. Expected is one of ( section, order ).\nLine: 9\n"]
            ]
        ];
    }
}
