<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config\Consolidated;

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
    public static function exemplarXmlDataProvider()
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
                [
                    "Element 'config': Missing child element(s). Expected is ( integration ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config/>\n2:\n"
                ],
            ],
            'empty integration' => [
                '<config>
                    <integration name="TestIntegration" />
                </config>',
                [
                    "Element 'integration': Missing child element(s). Expected is one of ( email, endpoint_url, " .
                    "identity_link_url, resources ).The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n" .
                    "2:                    <integration name=\"TestIntegration\"/>\n3:                " .
                    "</config>\n4:\n"
                ],
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
                [
                    "Element 'integration': Missing child element(s). Expected is ( email ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <endpoint_url>http://endpoint.url" .
                    "</endpoint_url>\n4:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n5:                        <resources>\n6:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n7:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n8:                        </resources>\n" .
                    "9:                    </integration>\n"
                ],
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
                [
                    "Element 'resources': Missing child element(s). Expected is ( resource ).The xml was: \n" .
                    "1:<config>\n2:                    <integration name=\"TestIntegration1\">\n" .
                    "3:                        <email>test-integration1@magento.com</email>\n" .
                    "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                        " .
                    "</resources>\n8:                    </integration>\n9:                </config>\n10:\n"
                ],
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
                    "Element 'email': [facet 'pattern'] The value '' is not accepted by the pattern " .
                    "'[^@]+@[^\.]+\..+'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n" .
                    "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                    "<email/>\n4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n"
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
                    "Element 'endpoint_url': [facet 'minLength'] The value has a length of '0'; this " .
                    "underruns the allowed minimum length of '4'.The xml was: \n0:<?xml version=\"1.0\"?>\n" .
                    "1:<config>\n2:                    <integration name=\"TestIntegration1\">\n" .
                    "3:                        <email>test-integration1@magento.com</email>\n" .
                    "4:                        <endpoint_url/>\n5:                        <resources>\n" .
                    "6:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "7:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "8:                        </resources>\n9:                    </integration>\n"
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
                    "Element 'identity_link_url': [facet 'minLength'] The value has a length of '0'; this " .
                    "underruns the allowed minimum length of '4'.The xml was: \n0:<?xml version=\"1.0\"?>\n" .
                    "1:<config>\n2:                    <integration name=\"TestIntegration1\">\n" .
                    "3:                        <email>test-integration1@magento.com</email>\n" .
                    "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url/>\n6:                        <resources>\n" .
                    "7:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "8:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "9:                        </resources>\n"
                ],
            ],
            /** Invalid structure */
            'irrelevant root node' => [
                '<integration name="TestIntegration"/>',
                [
                    "Element 'integration': No matching global declaration available for the validation root." .
                    "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integration name=\"TestIntegration\"/>\n2:\n"
                ],
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
                [
                    "Element 'invalid': This element is not expected. Expected is ( integration ).The xml was: \n" .
                    "6:                        <resources>\n7:                            <resource " .
                    "name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n" .
                    "10:                    </integration>\n11:                    <invalid/>\n" .
                    "12:                </config>\n13:\n"
                ],
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
                [
                    "Element 'invalid': This element is not expected.The xml was: \n5:                        " .
                    "<identity_link_url>http://www.example.com/identity</identity_link_url>\n" .
                    "6:                        <resources>\n7:                            <resource " .
                    "name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n" .
                    "10:                        <invalid/>\n11:                    </integration>\n" .
                    "12:                </config>\n13:\n"
                ],
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
                [
                    "Element 'invalid': This element is not expected. Expected is ( resource ).The xml was: \n" .
                    "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                            <invalid/>\n" .
                    "10:                        </resources>\n11:                    </integration>\n" .
                    "12:                </config>\n13:\n"
                ],
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
                    "Element 'resource': Element content is not allowed, because the content type is a simple " .
                    "type definition.The xml was: \n3:                        <email>test-integration1@magento.com" .
                    "</email>\n4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\">\n9:                                <invalid/>\n" .
                    "10:                            </resource>\n11:                        </resources>\n" .
                    "12:                    </integration>\n"
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
                [
                    "Element 'config', attribute 'invalid': The attribute 'invalid' is not allowed.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config invalid=\"invalid\">\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <email>test-integration1@magento.com" .
                    "</email>\n4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n"
                ],
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
                [
                    "Element 'integration', attribute 'invalid': The attribute 'invalid' is not allowed.The " .
                    "xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <integration " .
                    "name=\"TestIntegration1\" invalid=\"invalid\">\n3:                        <email>" .
                    "test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                    "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                    "http://www.example.com/identity</identity_link_url>\n6:                        <resources>\n" .
                    "7:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "8:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "9:                        </resources>\n"
                ],
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
                [
                    "Element 'email', attribute 'invalid': The attribute 'invalid' is not allowed.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <email invalid=\"invalid\">" .
                    "test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                    "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                    "http://www.example.com/identity</identity_link_url>\n6:                        " .
                    "<resources>\n7:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "8:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "9:                        </resources>\n"
                ],
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
                [
                    "Element 'resources', attribute 'invalid': The attribute 'invalid' is not allowed.The xml " .
                    "was: \n1:<config>\n2:                    <integration name=\"TestIntegration1\">\n" .
                    "3:                        <email>test-integration1@magento.com</email>\n" .
                    "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources invalid=\"invalid\">\n" .
                    "7:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "8:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "9:                        </resources>\n10:                    </integration>\n"
                ],
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
                [
                    "Element 'resource', attribute 'invalid': The attribute 'invalid' is not allowed.The xml was: \n" .
                    "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                    "<email>test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                    "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                    "http://www.example.com/identity</identity_link_url>\n6:                        <resources>\n" .
                    "7:                            <resource name=\"Magento_Customer::manage\" " .
                    "invalid=\"invalid\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n" .
                    "10:                    </integration>\n11:                </config>\n"
                ],
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
                [
                    "Element 'endpoint_url', attribute 'invalid': The attribute 'invalid' is not allowed.The " .
                    "xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <email>test-integration1@magento.com" .
                    "</email>\n4:                        <endpoint_url invalid=\"invalid\">http://endpoint.url" .
                    "</endpoint_url>\n5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n"
                ],
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
                [
                    "Element 'identity_link_url', attribute 'invalid': The attribute 'invalid' is not allowed.The " .
                    "xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <email>test-integration1@magento.com" .
                    "</email>\n4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url invalid=\"invalid\">http://endpoint.url" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n"
                ],
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
                [
                    "Element 'integration': The attribute 'name' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <integration>\n" .
                    "3:                        <email>test-integration1@magento.com</email>\n" .
                    "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n"
                ],
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
                    "Element 'integration', attribute 'name': [facet 'minLength'] The value '' has a length " .
                    "of '0'; this underruns the allowed minimum length of '2'.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <integration name=\"\">\n" .
                    "3:                        <email>test-integration1@magento.com</email>\n" .
                    "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n"
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
                [
                    "Element 'resource': The attribute 'name' is required but missing.The xml was: \n" .
                    "3:                        <email>test-integration1@magento.com</email>\n" .
                    "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                    "5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource/>\n" .
                    "9:                        </resources>\n10:                    </integration>\n" .
                    "11:                </config>\n12:\n"
                ],
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
                    "Element 'resource', attribute 'name': [facet 'pattern'] The value '' is not accepted by " .
                    "the pattern '.+_.+::.+'.The xml was: \n3:                        <email>" .
                    "test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                    "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                    "http://www.example.com/identity</identity_link_url>\n6:                        <resources>\n" .
                    "7:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "8:                            <resource name=\"\"/>\n9:                        </resources>\n" .
                    "10:                    </integration>\n11:                </config>\n12:\n"
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
                    "Element 'email': [facet 'pattern'] The value 'invalid' is not accepted by the " .
                    "pattern '[^@]+@[^\.]+\..+'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n" .
                    "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                    "<email>invalid</email>\n4:                        <endpoint_url>http://endpoint.url" .
                    "</endpoint_url>\n5:                        <identity_link_url>http://www.example.com/identity" .
                    "</identity_link_url>\n6:                        <resources>\n7:                            " .
                    "<resource name=\"Magento_Customer::manage\"/>\n8:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n9:                        </resources>\n"
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
                    "Element 'resource', attribute 'name': [facet 'pattern'] The value 'customer_manage' is " .
                    "not accepted by the pattern '.+_.+::.+'.The xml was: \n3:                        <email>" .
                    "test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                    "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                    "http://www.example.com/identity</identity_link_url>\n6:                        <resources>\n" .
                    "7:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "8:                            <resource name=\"customer_manage\"/>\n" .
                    "9:                        </resources>\n10:                    </integration>\n" .
                    "11:                </config>\n12:\n"
                ],
            ]
        ];
    }
}
