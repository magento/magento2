<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config\Initial;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * Path to xsd schema file
     * @var string
     */
    protected $xsdSchema;

    /**
     * @var XsdValidator
     */
    protected $xsdValidator;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->xsdSchema = $urnResolver->getRealPath('urn:magento:module:Magento_Store:etc/config.xsd');
        $this->xsdValidator = new XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidXml($xmlString, $expectedError)
    {
        $actualError = $this->xsdValidator->validate($this->xsdSchema, $xmlString);
        $this->assertEquals($expectedError, $actualError);
    }

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/valid_config.xml');
        $actualResult = $this->xsdValidator->validate($this->xsdSchema, $xmlString);
        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to config.xsd
     */
    public static function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidConfigXmlArray.php';
    }
}
