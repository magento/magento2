<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\GraphQl\Test\Unit\Model\Config;

class XsdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Path to xsd schema file
     * @var string
     */
    private $xsdSchema;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Utility\XsdValidator
     */
    private $xsdValidator;

    protected function setUp() : void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->xsdSchema = $urnResolver->getRealPath('urn:magento:module:Magento_GraphQl:etc/graphql.xsd');
        $this->xsdValidator = new \Magento\Framework\TestFramework\Unit\Utility\XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @return void
     * @dataProvider schemaCorrectlyIdentifiesInvalidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidXml($xmlString, $expectedError) : void
    {
        $actualError = $this->xsdValidator->validate($this->xsdSchema, $xmlString);
        $this->assertEquals($expectedError, $actualError);
    }

    /**
     * @return void
     */
    public function testSchemaCorrectlyIdentifiesValidXml() : void
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/graphql_simple_configurable.xml');
        $actualResult = $this->xsdValidator->validate($this->xsdSchema, $xmlString);
        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to graphql.xsd
     * @return array
     */
    public function schemaCorrectlyIdentifiesInvalidXmlDataProvider() : array
    {
        return include __DIR__ . '/_files/invalidGraphQlXmlArray.php';
    }
}
