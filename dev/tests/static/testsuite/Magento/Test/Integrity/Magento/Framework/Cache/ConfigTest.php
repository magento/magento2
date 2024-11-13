<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Test\Integrity\Magento\Framework\Cache;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of the cache configuration
 */
class ConfigTest extends TestCase
{
    /**
     * Path to xsd schema file
     * @var string
     */
    private $xsdSchema;

    /**
     * @var UrnResolver
     */
    private $urnResolver;

    /**
     * @var XsdValidator
     */
    private $xsdValidator;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->urnResolver = new UrnResolver();
        $this->xsdSchema = $this->urnResolver->getRealPath(
            'urn:magento:framework:Cache/etc/cache.xsd'
        );
        $this->xsdValidator = new XsdValidator();
    }

    /**
     * Tests invalid configurations
     *
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidXml(
        string $xmlString,
        array $expectedError
    ): void {
        $actualError = $this->xsdValidator->validate(
            $this->xsdSchema,
            $xmlString
        );
        $this->assertEquals($expectedError, $actualError);
    }

    /**
     * Tests valid configurations
     */
    public function testSchemaCorrectlyIdentifiesValidXml(): void
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/valid_cache_config.xml');
        $actualResult = $this->xsdValidator->validate(
            $this->xsdSchema,
            $xmlString
        );

        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to cache.xsd
     */
    public static function schemaCorrectlyIdentifiesInvalidXmlDataProvider(): array
    {
        return include __DIR__ . '/_files/invalidCacheConfigXmlArray.php';
    }
}
