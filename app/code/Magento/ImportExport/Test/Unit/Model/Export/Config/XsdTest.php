<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Export\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * Path to xsd file
     * @var string
     */
    protected $_xsdSchemaPath;

    /**
     * @var XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->_xsdSchemaPath = $urnResolver->getRealPath('urn:magento:module:Magento_ImportExport:etc/');
        $this->_xsdValidator = new XsdValidator();
    }

    /**
     * @param string $schemaName
     * @param string $xmlString
     * @param array $expectedError
     */
    protected function _loadDataForTest($schemaName, $xmlString, $expectedError)
    {
        $actualError = $this->_xsdValidator->validate($this->_xsdSchemaPath . $schemaName, $xmlString);
        $this->assertEquals(false, empty($actualError));
        foreach ($expectedError as $error) {
            $this->assertContains($error, $actualError);
        }
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesExportOptionsDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidProductOptionsXml($xmlString, $expectedError)
    {
        $actualErrors = $this->_xsdValidator->validate($this->_xsdSchemaPath . 'export.xsd', $xmlString);
        $this->assertNotEmpty($actualErrors);
        foreach ($expectedError as [$error, $isRegex]) {
            if ($isRegex) {
                $matched = false;
                foreach ($actualErrors as $actualError) {
                    try {
                        $this->assertMatchesRegularExpression($error, $actualError);
                        $matched = true;
                        break;
                    } catch (AssertionFailedError) {
                    }
                }
                $this->assertTrue($matched, "None of the errors matched: $error");
            } else {
                $this->assertContains($error, $actualErrors);
            }
        }
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
    public static function schemaCorrectlyIdentifiesValidXmlDataProvider()
    {
        return [
            'product_options' => ['export.xsd', 'export_valid.xml'],
            'product_options_merged' => ['export_merged.xsd', 'export_merged_valid.xml']
        ];
    }

    /**
     * Data provider with invalid xml array according to schema
     */
    public static function schemaCorrectlyIdentifiesExportOptionsDataProvider()
    {
        return include __DIR__ . '/_files/invalidExportXmlArray.php';
    }

    /**
     * Data provider with invalid xml array according to schema
     */
    public static function schemaCorrectlyIdentifiesInvalidExportMergedXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidExportMergedXmlArray.php';
    }
}
