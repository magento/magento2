<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return array(array('sales_valid.xml'));
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

        $actualErrors = array();
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
        return array(
            array(
                'sales_invalid.xml',
                array(
                    "Element 'section', attribute 'wrongName': The attribute 'wrongName' is not allowed.\n",
                    "Element 'section': The attribute 'name' is required but missing.\n",
                    "Element 'wrongGroup': This element is not expected. Expected is ( group ).\n"
                )
            ),
            array(
                'sales_invalid_duplicates.xml',
                array(
                    "Element 'renderer': Duplicate key-sequence ['r1']" .
                    " in unique identity-constraint 'uniqueRendererName'.\n",
                    "Element 'item': Duplicate key-sequence ['i1'] in unique identity-constraint 'uniqueItemName'.\n",
                    "Element 'group': Duplicate key-sequence ['g1'] in unique identity-constraint 'uniqueGroupName'.\n",
                    "Element 'section': Duplicate key-sequence ['s1']" .
                    " in unique identity-constraint 'uniqueSectionName'.\n",
                    "Element 'available_product_type': Duplicate key-sequence ['a1']" .
                    " in unique identity-constraint 'uniqueProductTypeName'.\n"
                )
            ),
            array(
                'sales_invalid_without_attributes.xml',
                array(
                    "Element 'section': The attribute 'name' is required but missing.\n",
                    "Element 'group': The attribute 'name' is required but missing.\n",
                    "Element 'item': The attribute 'name' is required but missing.\n",
                    "Element 'renderer': The attribute 'name' is required but missing.\n",
                    "Element 'renderer': The attribute 'instance' is required but missing.\n",
                    "Element 'available_product_type': The attribute 'name' is required but missing.\n"
                )
            ),
            array(
                'sales_invalid_root_node.xml',
                array("Element 'wrong': This element is not expected. Expected is one of ( section, order ).\n")
            )
        );
    }
}
