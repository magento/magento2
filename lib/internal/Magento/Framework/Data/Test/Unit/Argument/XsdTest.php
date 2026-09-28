<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Argument;

use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class XsdTest extends TestCase
{
    /**
     * Path to xsd schema file for validating argument types
     * @var string
     */
    protected $_typesXsdSchema;

    /**
     * @var XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_typesXsdSchema = __DIR__ . "/_files/types_schema.xsd";
        $this->_xsdValidator = new XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError     */
    #[DataProvider('schemaCorrectlyIdentifiesInvalidTypesXmlDataProvider')]
    public function testSchemaCorrectlyIdentifiesInvalidTypesXml($xmlString, $expectedError)
    {
        $actualError = $this->_xsdValidator->validate($this->_typesXsdSchema, $xmlString);
        $this->assertEquals($expectedError, $actualError);
    }

    /**
     * Data provider with invalid type declaration
     *
     * @return array
     */
    public static function schemaCorrectlyIdentifiesInvalidTypesXmlDataProvider()
    {
        return include __DIR__ . '/_files/typesInvalidArray.php';
    }

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/types_valid.xml');
        $actualResult = $this->_xsdValidator->validate($this->_typesXsdSchema, $xmlString);

        $this->assertEmpty($actualResult, join("\n", $actualResult));
    }
}
