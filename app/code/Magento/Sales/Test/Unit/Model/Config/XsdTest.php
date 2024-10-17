<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Config;

use Magento\Framework\Config\Dom;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * @var string
     */
    protected $_xsdFile;

    protected function setUp(): void
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
        $result = Dom::validateDomDocument($dom, $this->_xsdFile);
        libxml_use_internal_errors(false);
        $this->assertEmpty($result, 'Validation failed with errors: ' . join(', ', $result));
    }

    /**
     * @return array
     */
    public static function validXmlFileDataProvider()
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

        $result = Dom::validateDomDocument($dom, $this->_xsdFile);

        libxml_use_internal_errors(false);
        $this->assertEquals($expectedErrors, $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function invalidXmlFileDataProvider()
    {
        return [
            [
                'sales_invalid.xml',
                [
                    "Element 'section', attribute 'wrongName': The attribute 'wrongName' is not allowed.\nLine: 9\n" .
                    "The xml was: \n4: * See COPYING.txt for license details.\n5: */\n6:-->\n" .
                    "7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n8:    " .
                    "<section wrongName=\"section1\">\n9:        <wrongGroup wrongName=\"group1\"/>\n" .
                    "10:    </section>\n11:</config>\n12:\n",
                    "Element 'section': The attribute 'name' is required but missing.\nLine: 9\n" .
                    "The xml was: \n4: * See COPYING.txt for license details.\n5: */\n6:-->\n" .
                    "7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n8:    " .
                    "<section wrongName=\"section1\">\n9:        <wrongGroup wrongName=\"group1\"/>\n" .
                    "10:    </section>\n11:</config>\n12:\n",
                    "Element 'wrongGroup': This element is not expected. Expected is ( group ).\nLine: 10\n" .
                    "The xml was: \n5: */\n6:-->\n" .
                    "7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n8:    " .
                    "<section wrongName=\"section1\">\n9:        <wrongGroup wrongName=\"group1\"/>\n" .
                    "10:    </section>\n11:</config>\n12:\n"
                ],
            ],
            [
                'sales_invalid_duplicates.xml',
                [
                    "Element 'renderer': Duplicate key-sequence ['r1'] in unique identity-constraint " .
                    "'uniqueRendererName'.\nLine: 13\nThe xml was: \n8:    <section name=\"s1\">\n9:        " .
                    "<group name=\"g1\">\n10:            <item name=\"i1\" instance=\"instance1\" " .
                    "sort_order=\"1\">\n11:                <renderer name=\"r1\" instance=\"instance1\"/>\n" .
                    "12:                <renderer name=\"r1\" instance=\"instance1\"/>\n13:            </item>\n" .
                    "14:            <item name=\"i1\" instance=\"instance1\" sort_order=\"1\"/>\n15:        " .
                    "</group>\n16:        <group name=\"g1\">\n17:            <item name=\"i1\" " .
                    "instance=\"instance1\" sort_order=\"1\"/>\n",
                    "Element 'item': Duplicate key-sequence ['i1'] in unique identity-constraint 'uniqueItemName'.\n" .
                    "Line: 15\nThe xml was: \n10:            <item name=\"i1\" instance=\"instance1\" " .
                    "sort_order=\"1\">\n11:                <renderer name=\"r1\" instance=\"instance1\"/>\n" .
                    "12:                <renderer name=\"r1\" instance=\"instance1\"/>\n13:            </item>\n" .
                    "14:            <item name=\"i1\" instance=\"instance1\" sort_order=\"1\"/>\n15:        " .
                    "</group>\n16:        <group name=\"g1\">\n17:            <item name=\"i1\" " .
                    "instance=\"instance1\" sort_order=\"1\"/>\n18:        </group>\n19:    </section>\n",
                    "Element 'group': Duplicate key-sequence ['g1'] in unique identity-constraint " .
                    "'uniqueGroupName'.\nLine: 17\nThe xml was: \n12:                <renderer name=\"r1\" " .
                    "instance=\"instance1\"/>\n13:            </item>\n14:            <item name=\"i1\" " .
                    "instance=\"instance1\" sort_order=\"1\"/>\n15:        </group>\n16:        <group " .
                    "name=\"g1\">\n17:            <item name=\"i1\" instance=\"instance1\" sort_order=\"1\"/>\n" .
                    "18:        </group>\n19:    </section>\n20:    <section name=\"s1\">\n21:        <group " .
                    "name=\"g1\">\n",
                    "Element 'section': Duplicate key-sequence ['s1'] in unique identity-constraint " .
                    "'uniqueSectionName'.\nLine: 21\nThe xml was: \n16:        <group name=\"g1\">\n" .
                    "17:            <item name=\"i1\" instance=\"instance1\" sort_order=\"1\"/>\n18:        " .
                    "</group>\n19:    </section>\n20:    <section name=\"s1\">\n21:        <group name=\"g1\">\n" .
                    "22:            <item name=\"i1\" instance=\"instance1\" sort_order=\"1\"/>\n23:        " .
                    "</group>\n24:    </section>\n25:    <order>\n",
                    "Element 'available_product_type': Duplicate key-sequence ['a1'] in unique " .
                    "identity-constraint 'uniqueProductTypeName'.\nLine: 28\nThe xml was: \n23:        </group>\n" .
                    "24:    </section>\n25:    <order>\n26:        <available_product_type name=\"a1\"/>\n" .
                    "27:        <available_product_type name=\"a1\"/>\n28:    </order>\n29:</config>\n30:\n"
                ]
            ],
            [
                'sales_invalid_without_attributes.xml',
                [
                    "Element 'section': The attribute 'name' is required but missing.\nLine: 9\nThe xml was: \n" .
                    "4: * See COPYING.txt for license details.\n5: */\n6:-->\n7:<config " .
                    "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n8:    " .
                    "<section>\n9:        <group>\n10:            <item>\n11:                " .
                    "<renderer/>\n12:            </item>\n13:        </group>\n",
                    "Element 'group': The attribute 'name' is required but missing.\nLine: 10\nThe xml was: \n" .
                    "5: */\n6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n" .
                    "8:    <section>\n9:        <group>\n10:            <item>\n11:                " .
                    "<renderer/>\n12:            </item>\n13:        </group>\n14:    </section>\n",
                    "Element 'item': The attribute 'name' is required but missing.\nLine: 11\nThe xml was: \n" .
                    "6:-->\n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n8:    " .
                    "<section>\n9:        <group>\n10:            <item>\n11:                " .
                    "<renderer/>\n12:            </item>\n13:        </group>\n14:    </section>\n" .
                    "15:    <order>\n",
                    "Element 'renderer': The attribute 'name' is required but missing.\nLine: 12\nThe xml was: \n" .
                    "7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n8:    " .
                    "<section>\n9:        <group>\n10:            <item>\n11:                " .
                    "<renderer/>\n12:            </item>\n13:        </group>\n14:    </section>\n" .
                    "15:    <order>\n16:        <available_product_type/>\n",
                    "Element 'renderer': The attribute 'instance' is required but missing.\nLine: 12\nThe xml " .
                    "was: \n7:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n" .
                    "8:    <section>\n9:        <group>\n10:            <item>\n11:                " .
                    "<renderer/>\n12:            </item>\n13:        </group>\n14:    </section>\n" .
                    "15:    <order>\n16:        <available_product_type/>\n",
                    "Element 'available_product_type': The attribute 'name' is required but missing.\nLine: 17\n" .
                    "The xml was: \n12:            </item>\n13:        </group>\n14:    </section>\n15:    " .
                    "<order>\n16:        <available_product_type/>\n17:    </order>\n18:</config>\n19:\n"
                ]
            ],
            [
                'sales_invalid_root_node.xml',
                [
                    "Element 'wrong': This element is not expected. Expected is one of ( section, order ).\n" .
                    "Line: 9\nThe xml was: \n4: * See COPYING.txt for license details.\n5: */\n6:-->\n7:<config " .
                    "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " .
                    "xsi:noNamespaceSchemaLocation=\"urn:magento:module:Magento_Sales:etc/sales.xsd\">\n8:    " .
                    "<wrong/>\n9:</config>\n10:\n"
                ]
            ]
        ];
    }
}
