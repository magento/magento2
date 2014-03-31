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
namespace Magento\Catalog\Model\ProductOptions\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to xsd file
     * @var string
     */
    protected $_xsdSchemaPath;

    /**
     * @var \Magento\TestFramework\Utility\XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp()
    {
        $this->_xsdSchemaPath = BP . '/app/code/Magento/Catalog/etc/';
        $this->_xsdValidator = new \Magento\TestFramework\Utility\XsdValidator();
    }

    /**
     * @param string $schemaName
     * @param string $xmlString
     * @param array $expectedError
     */
    protected function _loadDataForTest($schemaName, $xmlString, $expectedError)
    {
        $actualError = $this->_xsdValidator->validate($this->_xsdSchemaPath . $schemaName, $xmlString);
        $this->assertEquals($expectedError, $actualError);
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidProductOptionsDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidProductOptionsXml($xmlString, $expectedError)
    {
        $this->_loadDataForTest('product_options.xsd', $xmlString, $expectedError);
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidProductOptionsMergedXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidProductOptionsMergedXml($xmlString, $expectedError)
    {
        $this->_loadDataForTest('product_options_merged.xsd', $xmlString, $expectedError);
    }

    /**
     * @param string $schemaName
     * @param string $validFileName
     * @dataProvider schemaCorrectlyIdentifiesValidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesValidXml($schemaName, $validFileName)
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/' . $validFileName);
        $schemaPath = $this->_xsdSchemaPath . $schemaName;
        $actualResult = $this->_xsdValidator->validate($schemaPath, $xmlString);
        $this->assertEquals(array(), $actualResult);
    }

    /**
     * Data provider with valid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesValidXmlDataProvider()
    {
        return array(
            'product_options' => array('product_options.xsd', 'product_options_valid.xml'),
            'product_options_merged' => array('product_options_merged.xsd', 'product_options_merged_valid.xml')
        );
    }

    /**
     * Data provider with invalid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesInvalidProductOptionsDataProvider()
    {
        return include __DIR__ . '/_files/invalidProductOptionsXmlArray.php';
    }

    /**
     * Data provider with invalid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesInvalidProductOptionsMergedXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidProductOptionsMergedXmlArray.php';
    }
}
