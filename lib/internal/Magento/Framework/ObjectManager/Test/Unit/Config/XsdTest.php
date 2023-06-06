<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Config;

use Magento\Framework\ObjectManager\Config\SchemaLocator;
use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * @var SchemaLocator
     */
    protected $_schemaLocator;

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
        $this->_schemaLocator = new SchemaLocator();
        $this->_xsdSchema = $this->_schemaLocator->getSchema();
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

    /**
     * Get array of invalid xml strings
     *
     * @return array
     */
    public function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidConfigXmlArray.php';
    }

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/valid_config.xml');
        $actualResult = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);

        $this->assertEmpty($actualResult, join("\n", $actualResult));
    }
}
