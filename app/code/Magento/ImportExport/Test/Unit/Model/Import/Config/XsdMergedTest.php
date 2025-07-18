<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class XsdMergedTest extends TestCase
{
    /**
     * Path to xsd schema file
     * @var string
     */
    protected $_xsdSchema;

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
        $this->_xsdSchema = $urnResolver->getRealPath('urn:magento:module:Magento_ImportExport:etc/import_merged.xsd');
        $this->_xsdValidator = new XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidXml($xmlString, $expectedError)
    {
        $actualErrors = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);
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

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/valid_import_merged.xml');
        $actualResult = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);

        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to import_merged.xsd
     */
    public static function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidImportMergedXmlArray.php';
    }
}
