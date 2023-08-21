<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for validation rules implemented by XSD schema for integration configuration.
 */
class XsdTest extends TestCase
{
    /**
     * @var string
     */
    protected $schemaFile;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->schemaFile = $urnResolver->getRealPath(
            'urn:magento:module:Magento_Integration:etc/integration/config.xsd'
        );
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new Dom($fixtureXml, $validationStateMock, [], null, null, $messageFormat);
        $actualResult = $dom->validate($this->schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
        foreach ($expectedErrors as $error) {
            $this->assertContains($error, $actualErrors, "Validation errors does not match.");
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exemplarXmlDataProvider()
    {
        return [
            /** Valid configurations */
            'valid' => [
                '<integrations>
                    <integration name="TestIntegration">
                        <email>test-integration@magento.com</email>
                        <endpoint_url>https://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [],
            ],
            'valid with several entities' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                    <integration name="TestIntegration2">
                        <email>test-integration2@magento.com</email>
                    </integration>
                </integrations>',
                [],
            ],
            /** Missing required elements */
            'empty root node' => [
                '<integrations/>',
                ["Element 'integrations': Missing child element(s). Expected is ( integration )."],
            ],
            'empty integration' => [
                '<integrations>
                    <integration name="TestIntegration" />
                </integrations>',
                ["Element 'integration': Missing child element(s). Expected is ( email )."],
            ],
            'integration without email' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                ["Element 'endpoint_url': This element is not expected. Expected is ( email )."],
            ],
            /** Empty nodes */
            'empty email' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email></email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [
                    "Element 'email': [facet 'pattern'] The value '' is not " .
                    "accepted by the pattern '[^@]+@[^\.]+\..+'."
                ],
            ],
            'endpoint_url is empty' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url></endpoint_url>
                    </integration>
                </integrations>',
                [
                    "Element 'endpoint_url': [facet 'minLength'] The value has a length of '0'; this underruns" .
                    " the allowed minimum length of '4'."
                ],
            ],
            'identity_link_url is empty' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url></identity_link_url>
                    </integration>
                </integrations>',
                [
                    "Element 'identity_link_url': [facet 'minLength'] The value has a length of '0'; this underruns" .
                    " the allowed minimum length of '4'."
                ],
            ],
            /** Invalid structure */
            'irrelevant root node' => [
                '<integration name="TestIntegration"/>',
                ["Element 'integration': No matching global declaration available for the validation root."],
            ],
            'irrelevant node in root' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                    <invalid/>
                </integrations>',
                ["Element 'invalid': This element is not expected. Expected is ( integration )."],
            ],
            'irrelevant node in integration' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <invalid/>
                    </integration>
                </integrations>',
                ["Element 'invalid': This element is not expected."],
            ],
            'irrelevant node in authentication' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <invalid/>
                    </integration>
                </integrations>',
                ["Element 'invalid': This element is not expected."],
            ],
            /** Excessive attributes */
            'invalid attribute in root' => [
                '<integrations invalid="invalid">
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                ["Element 'integrations', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in integration' => [
                '<integrations>
                    <integration name="TestIntegration1" invalid="invalid">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                ["Element 'integration', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in email' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email invalid="invalid">test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                ["Element 'email', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in endpoint_url' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url invalid="invalid">http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                ["Element 'endpoint_url', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in identity_link_url' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url invalid="invalid">http://endpoint.url</identity_link_url>
                    </integration>
                </integrations>',
                ["Element 'identity_link_url', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            /** Missing or empty required attributes */
            'integration without name' => [
                '<integrations>
                    <integration>
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                ["Element 'integration': The attribute 'name' is required but missing."],
            ],
            'integration with empty name' => [
                '<integrations>
                    <integration name="">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [
                    "Element 'integration', attribute 'name': [facet 'minLength'] The value '' has a length of '0'; " .
                    "this underruns the allowed minimum length of '2'."
                ],
            ],
            /** Invalid values */
            'invalid email' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>invalid</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [
                    "Element 'email': [facet 'pattern'] The value 'invalid' " .
                    "is not accepted by the pattern '[^@]+@[^\.]+\..+'."
                ],
            ]
        ];
    }
}
