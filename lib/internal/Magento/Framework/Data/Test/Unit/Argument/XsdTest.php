<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Argument;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to xsd schema file for validating argument types
     * @var string
     */
    protected $_typesXsdSchema;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Utility\XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_typesXsdSchema = __DIR__ . "/_files/types_schema.xsd";
        $this->_xsdValidator = new \Magento\Framework\TestFramework\Unit\Utility\XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidTypesXmlDataProvider
     */
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
    public function schemaCorrectlyIdentifiesInvalidTypesXmlDataProvider()
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
