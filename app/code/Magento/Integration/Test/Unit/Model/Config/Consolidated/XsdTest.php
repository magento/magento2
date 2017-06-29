<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Config\Consolidated;

/**
 * Test for validation rules implemented by XSD schema for integration configuration.
 */
class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $schemaFile;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->schemaFile = $urnResolver->getRealPath(
            'urn:magento:module:Magento_Integration:etc/integration/integration.xsd'
        );
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationStateMock = $this->getMock(
            \Magento\Framework\Config\ValidationStateInterface::class,
            [],
            [],
            '',
            false
        );
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, $validationStateMock, [], null, null, $messageFormat);
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
                '<config>
                    <integration name="TestIntegration">
                        <email>test-integration@magento.com</email>
                        <endpoint_url>https://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                [],
            ],
            'valid with several entities' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                    <integration name="TestIntegration2">
                        <email>test-integration2@magento.com</email>
                        <resources>
                            <resource name="Magento_Catalog::product_read" />
                        </resources>
                    </integration>
                </config>',
                [],
            ],
            /** Missing required elements */
            'empty root node' => [
                '<config/>',
                ["Element 'config': Missing child element(s). Expected is ( integration )."],
            ],
            'empty integration' => [
                '<config>
                    <integration name="TestIntegration" />
                </config>',
                ["Element 'integration': Missing child element(s)." .
                 " Expected is one of ( email, endpoint_url, identity_link_url, resources )."],
            ],
            'integration without email' => [
                '<config>
                    <integration name="TestIntegration1">
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'integration': Missing child element(s). Expected is ( email )."],
            ],
            'empty resources' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                        </resources>
                    </integration>
                </config>',
                ["Element 'resources': Missing child element(s). Expected is ( resource )."],
            ],
            /** Empty nodes */
            'empty email' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email></email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                [
                    "Element 'email': [facet 'pattern'] The value '' is not " .
                    "accepted by the pattern '[^@]+@[^\.]+\..+'.",
                    "Element 'email': '' is not a valid value of the atomic type 'emailType'."
                ],
            ],
            'endpoint_url is empty' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url></endpoint_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                [
                    "Element 'endpoint_url': [facet 'minLength'] The value has a length of '0'; this underruns" .
                    " the allowed minimum length of '4'.",
                    "Element 'endpoint_url': '' is not a valid value of the atomic type 'urlType'."
                ],
            ],
            'identity_link_url is empty' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url></identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                [
                    "Element 'identity_link_url': [facet 'minLength'] The value has a length of '0'; this underruns" .
                    " the allowed minimum length of '4'.",
                    "Element 'identity_link_url': '' is not a valid value of the atomic type 'urlType'."
                ],
            ],
            /** Invalid structure */
            'irrelevant root node' => [
                '<integration name="TestIntegration"/>',
                ["Element 'integration': No matching global declaration available for the validation root."],
            ],
            'irrelevant node in root' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                    <invalid/>
                </config>',
                ["Element 'invalid': This element is not expected. Expected is ( integration )."],
            ],
            'irrelevant node in integration' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                        <invalid/>
                    </integration>
                </config>',
                ["Element 'invalid': This element is not expected."],
            ],
            'irrelevant node in resources' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                            <invalid/>
                        </resources>
                    </integration>
                </config>',
                ["Element 'invalid': This element is not expected. Expected is ( resource )."],
            ],
            'irrelevant node in resource' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online">
                                <invalid/>
                            </resource>
                        </resources>
                    </integration>
                </config>',
                [
                    "Element 'resource': Element content is not allowed, " .
                    "because the content type is a simple type definition."
                ],
            ],
            /** Excessive attributes */
            'invalid attribute in root' => [
                '<config invalid="invalid">
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'config', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in integration' => [
                '<config>
                    <integration name="TestIntegration1" invalid="invalid">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'integration', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in email' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email invalid="invalid">test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage"/>
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'email', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in resources' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources invalid="invalid">
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'resources', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in resource' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" invalid="invalid" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'resource', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in endpoint_url' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url invalid="invalid">http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage"/>
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'endpoint_url', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in identity_link_url' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url invalid="invalid">http://endpoint.url</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage"/>
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'identity_link_url', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            /** Missing or empty required attributes */
            'integration without name' => [
                '<config>
                    <integration>
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                ["Element 'integration': The attribute 'name' is required but missing."],
            ],
            'integration with empty name' => [
                '<config>
                    <integration name="">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                [
                    "Element 'integration', attribute 'name': [facet 'minLength'] The value '' has a length of '0'; " .
                    "this underruns the allowed minimum length of '2'.",
                    "Element 'integration', attribute 'name': " .
                    "'' is not a valid value of the atomic type 'integrationNameType'."
                ],
            ],
            'resource without name' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource />
                        </resources>
                    </integration>
                </config>',
                ["Element 'resource': The attribute 'name' is required but missing."],
            ],
            'resource with empty name' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="" />
                        </resources>
                    </integration>
                </config>',
                [
                    "Element 'resource', attribute 'name': [facet 'pattern'] " .
                    "The value '' is not accepted by the pattern '.+_.+::.+'.",
                    "Element 'resource', attribute 'name': '' " .
                    "is not a valid value of the atomic type 'resourceNameType'."
                ],
            ],
            /** Invalid values */
            'invalid email' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>invalid</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::manage" />
                            <resource name="Magento_Customer::online" />
                        </resources>
                    </integration>
                </config>',
                [
                    "Element 'email': [facet 'pattern'] The value 'invalid' " .
                    "is not accepted by the pattern '[^@]+@[^\.]+\..+'.",
                    "Element 'email': 'invalid' is not a valid value of the atomic type 'emailType'."
                ],
            ],
            /** Invalid values */
            'resource with invalid name' => [
                '<config>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <resources>
                            <resource name="Magento_Customer::online" />
                            <resource name="customer_manage" />
                        </resources>
                    </integration>
                </config>',
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
