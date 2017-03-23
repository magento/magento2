<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Export\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
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
        $this->_xsdSchemaPath = $urnResolver->getRealPath('urn:magento:module:Magento_ImportExport:etc/');
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
     * @dataProvider schemaCorrectlyIdentifiesExportOptionsDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidProductOptionsXml($xmlString, $expectedError)
    {
        $this->_loadDataForTest('export.xsd', $xmlString, $expectedError);
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidExportMergedXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidProductOptionsMergedXml($xmlString, $expectedError)
    {
        $this->_loadDataForTest('export_merged.xsd', $xmlString, $expectedError);
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
            'product_options' => ['export.xsd', 'export_valid.xml'],
            'product_options_merged' => ['export_merged.xsd', 'export_merged_valid.xml']
        ];
    }

    /**
     * Data provider with invalid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesExportOptionsDataProvider()
    {
        return include __DIR__ . '/_files/invalidExportXmlArray.php';
    }

    /**
     * Data provider with invalid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesInvalidExportMergedXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidExportMergedXmlArray.php';
    }
}
