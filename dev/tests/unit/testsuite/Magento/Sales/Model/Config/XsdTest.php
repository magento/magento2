<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    protected $_xsdFile;

    public function setUp()
    {
        $this->_xsdFile = __DIR__ . "/../../../../../../../../app/code/Magento/Sales/etc/sales.xsd";
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
        $result = $dom->schemaValidate($this->_xsdFile);
        libxml_use_internal_errors(false);
        $this->assertTrue($result);
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
        $dom->schemaValidate($this->_xsdFile);
        $errors = libxml_get_errors();

        $actualErrors = [];
        foreach ($errors as $error) {
            $actualErrors[] = $error->message;
        }
        libxml_use_internal_errors(false);
        $this->assertEquals($expectedErrors, $actualErrors);
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
                    "Element 'section', attribute 'wrongName': The attribute 'wrongName' is not allowed.\n",
                    "Element 'section': The attribute 'name' is required but missing.\n",
                    "Element 'wrongGroup': This element is not expected. Expected is ( group ).\n"
                ],
            ],
            [
                'sales_invalid_duplicates.xml',
                [
                    "Element 'renderer': Duplicate key-sequence ['r1']" .
                    " in unique identity-constraint 'uniqueRendererName'.\n",
                    "Element 'item': Duplicate key-sequence ['i1'] in unique identity-constraint 'uniqueItemName'.\n",
                    "Element 'group': Duplicate key-sequence ['g1'] in unique identity-constraint 'uniqueGroupName'.\n",
                    "Element 'section': Duplicate key-sequence ['s1']" .
                    " in unique identity-constraint 'uniqueSectionName'.\n",
                    "Element 'available_product_type': Duplicate key-sequence ['a1']" .
                    " in unique identity-constraint 'uniqueProductTypeName'.\n"
                ]
            ],
            [
                'sales_invalid_without_attributes.xml',
                [
                    "Element 'section': The attribute 'name' is required but missing.\n",
                    "Element 'group': The attribute 'name' is required but missing.\n",
                    "Element 'item': The attribute 'name' is required but missing.\n",
                    "Element 'renderer': The attribute 'name' is required but missing.\n",
                    "Element 'renderer': The attribute 'instance' is required but missing.\n",
                    "Element 'available_product_type': The attribute 'name' is required but missing.\n"
                ]
            ],
            [
                'sales_invalid_root_node.xml',
                ["Element 'wrong': This element is not expected. Expected is one of ( section, order ).\n"]
            ]
        ];
    }
}
