<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ProductOptions\Config;

class XsdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Path to xsd file
     * @var string
     */
    protected $_xsdSchemaPath;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Utility\XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->_xsdSchemaPath = $urnResolver->getRealPath('urn:magento:module:Magento_Catalog:etc/');
        $this->_xsdValidator = new \Magento\Framework\TestFramework\Unit\Utility\XsdValidator();
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
        $this->assertEquals([], $actualResult);
    }

    /**
     * Data provider with valid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesValidXmlDataProvider()
    {
        return [
            'product_options' => ['product_options.xsd', 'product_options_valid.xml'],
            'product_options_merged' => ['product_options_merged.xsd', 'product_options_merged_valid.xml']
        ];
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
