<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config\Integration;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for validation rules implemented by XSD schema for API integration configuration.
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
            'urn:magento:module:Magento_Integration:etc/integration/api.xsd'
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
        $this->assertEquals($expectedErrors, $actualErrors, "Validation errors does not match.");
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
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </integrations>',
                [],
            ],
            'valid with several entities' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                    <integration name="TestIntegration2">
                        <resources>
                            <resource name="Magento_Catalog::product_read" />
                        </resources>
                    </integration>
                </integrations>',
                [],
            ],
            /** Missing required nodes */
            'empty root node' => [
                '<integrations/>',
                ["Element 'integrations': Missing child element(s). Expected is ( integration )."],
            ],
            'empty integration' => [
                '<integrations>
                    <integration name="TestIntegration" />
                </integrations>',
                ["Element 'integration': Missing child element(s). Expected is ( resources )."],
            ],
            'empty resources' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'resources': Missing child element(s). Expected is ( resource )."],
            ],
            'irrelevant root node' => [
                '<integration name="TestIntegration"/>',
                ["Element 'integration': No matching global declaration available for the validation root."],
            ],
            /** Excessive nodes */
            'irrelevant node in root' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                    <invalid/>
                </integrations>',
                ["Element 'invalid': This element is not expected. Expected is ( integration )."],
            ],
            'irrelevant node in integration' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                        <invalid/>
                    </integration>
                </integrations>',
                ["Element 'invalid': This element is not expected."],
            ],
            'irrelevant node in resources' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        <invalid/>
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'invalid': This element is not expected. Expected is ( resource )."],
            ],
            'irrelevant node in resource' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online">
                                <invalid/>
                            </resource>
                        </resources>
                    </integration>
                </integrations>',
                [
                    "Element 'resource': Element content is not allowed, " .
                    "because the content type is a simple type definition."
                ],
            ],
            /** Excessive attributes */
            'invalid attribute in root' => [
                '<integrations invalid="invalid">
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'integrations', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in integration' => [
                '<integrations>
                    <integration name="TestIntegration1" invalid="invalid">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'integration', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in resources' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources invalid="invalid">
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'resources', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in resource' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" invalid="invalid" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'resource', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            /** Missing or empty required attributes */
            'integration without name' => [
                '<integrations>
                    <integration>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'integration': The attribute 'name' is required but missing."],
            ],
            'integration with empty name' => [
                '<integrations>
                    <integration name="">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </integrations>',
                [
                    "Element 'integration', attribute 'name': [facet 'minLength'] The value '' has a length of '0'; " .
                    "this underruns the allowed minimum length of '2'.",
                    "Element 'integration', attribute 'name': " .
                    "'' is not a valid value of the atomic type 'integrationNameType'."
                ],
            ],
            'resource without name' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource />
                        </resources>
                    </integration>
                </integrations>',
                ["Element 'resource': The attribute 'name' is required but missing."],
            ],
            'resource with empty name' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="" />
                        </resources>
                    </integration>
                </integrations>',
                [
                    "Element 'resource', attribute 'name': [facet 'pattern'] " .
                    "The value '' is not accepted by the pattern '.+_.+::.+'.",
                    "Element 'resource', attribute 'name': '' " .
                    "is not a valid value of the atomic type 'resourceNameType'."
                ],
            ],
            /** Invalid values */
            'resource with invalid name' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                            <resource name="Magento_Customer::online" />
                            <resource name="customer_manage" />
                        </resources>
                    </integration>
                </integrations>',
                [
                    "Element 'resource', attribute 'name': [facet 'pattern'] " .
                    "The value 'customer_manage' is not accepted by the pattern '.+_.+::.+'.",
                    "Element 'resource', attribute 'name': 'customer_manage' " .
                    "is not a valid value of the atomic type 'resourceNameType'."
                ],
            ]
        ];
    }
}
