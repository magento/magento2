<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
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
        $this->_xsdSchema = $urnResolver->getRealPath('urn:magento:framework:App/etc/routes.xsd');
        $this->_xsdValidator = new XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidXml($xmlString, $expectedError)
    {
        $actualError = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);
        $this->assertEquals(false, empty($actualError));
        foreach ($expectedError as $error) {
            $this->assertContains($error, $actualError);
        }
    }

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/valid_routes.xml');
        $actualResult = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);

        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to routes.xsd
     */
    public function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidRoutesXmlArray.php';
    }
}
