<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\AssertionFailedError;
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
        $this->assertEquals(empty($expectedErrors), empty($actualErrors));
        foreach ($expectedErrors as [$error, $isRegex]) {
            if ($isRegex) {
                $matched = false;
                foreach ($actualErrors as $actualError) {
                    try {
                        $this->assertMatchesRegularExpression($error, $actualError);
                        $matched = true;
                        break;
                    } catch (AssertionFailedError) {
                    }
                }
                $this->assertTrue($matched, "None of the errors matched: $error");
            } else {
                $this->assertContains($error, $actualErrors, "Validation errors does not match.");
            }
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
                [
                    [
                        "Element 'integrations': Missing child element(s). Expected is ( integration )." .
                        "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations/>\n2:\n",
                        false,
                    ],
                ],
            ],
            'empty integration' => [
                '<integrations>
                    <integration name="TestIntegration" />
                </integrations>',
                [
                    [
                        "Element 'integration': Missing child element(s). Expected is ( email ).The xml was: \n" .
                        "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                        "name=\"TestIntegration\"/>\n3:                </integrations>\n4:\n",
                        false,
                    ],
                ],
            ],
            'integration without email' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [
                    [
                        "Element 'endpoint_url': This element is not expected. Expected is ( email ).The xml was: \n" .
                        "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                        "name=\"TestIntegration1\">\n3:                        <endpoint_url>http://endpoint.url" .
                        "</endpoint_url>\n4:                        <identity_link_url>" .
                        "http://www.example.com/identity</identity_link_url>\n5:                    </integration>" .
                        "\n6:                </integrations>\n7:\n",
                        false,
                    ],
                ],
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
                    [
                        "Element 'email': [facet 'pattern'] The value '' is not accepted by the pattern " .
                        "'[^@]+@[^\.]+\..+'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n" .
                        "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                        "<email/>\n4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                        "5:                        <identity_link_url>http://www.example.com/identity" .
                        "</identity_link_url>\n6:                    </integration>\n7:                " .
                        "</integrations>\n8:\n",
                        false,
                    ],
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
                    [
                        "Element 'endpoint_url': [facet 'minLength'] The value has a length of '0'; this underruns " .
                        "the allowed minimum length of '4'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>" .
                        "\n2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                        "<email>test-integration1@magento.com</email>\n4:                        <endpoint_url/>\n" .
                        "5:                    </integration>\n6:                </integrations>\n7:\n",
                        false,
                    ],
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
                    [
                        "Element 'identity_link_url': [facet 'minLength'] The value has a length of '0'; this " .
                        "underruns the allowed minimum length of '4'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:" .
                        "<integrations>\n2:                    <integration name=\"TestIntegration1\">" .
                        "\n3:                        <email>test-integration1@magento.com</email>" .
                        "\n4:                        <endpoint_url>http://endpoint.url</endpoint_url>" .
                        "\n5:                        <identity_link_url/>\n6:                    </integration>" .
                        "\n7:                </integrations>\n8:\n",
                        false,
                    ],
                ],
            ],
            /** Invalid structure */
            'irrelevant root node' => [
                '<integration name="TestIntegration"/>',
                [
                    [
                        "Element 'integration': No matching global declaration available for the validation root." .
                        "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integration name=\"TestIntegration\"/>\n2:\n",
                        false,
                    ],
                ],
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
                [
                    [
                        "Element 'invalid': This element is not expected. Expected is ( integration ).The xml was: \n" .
                        "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                        "<email>test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                        "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                        "http://www.example.com/identity</identity_link_url>\n6:                    </integration>\n" .
                        "7:                    <invalid/>\n8:                </integrations>\n9:\n",
                        false,
                    ],
                ],
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
                [
                    [
                        "Element 'invalid': This element is not expected.The xml was: \n1:<integrations>\n" .
                        "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                        "<email>test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                        "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                        "http://www.example.com/identity</identity_link_url>\n6:                        <invalid/>\n" .
                        "7:                    </integration>\n8:                </integrations>\n9:\n",
                        false,
                    ],
                ],
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
                [
                    [
                        "Element 'invalid': This element is not expected.The xml was: \n1:<integrations>\n" .
                        "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                        "<email>test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                        "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                        "http://www.example.com/identity</identity_link_url>\n6:                        <invalid/>\n" .
                        "7:                    </integration>\n8:                </integrations>\n9:\n",
                        false,
                    ],
                ],
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
                [
                    [
                        "Element 'integrations', attribute 'invalid': The attribute 'invalid' is not allowed.The xml " .
                        "was: \n0:<?xml version=\"1.0\"?>\n1:<integrations invalid=\"invalid\">" .
                        "\n2:                    <integration name=\"TestIntegration1\">" .
                        "\n3:                        <email>test-integration1@magento.com</email>" .
                        "\n4:                        <endpoint_url>http://endpoint.url</endpoint_url>" .
                        "\n5:                        <identity_link_url>http://www.example.com/identity" .
                        "</identity_link_url>\n6:                    </integration>" .
                        "\n7:                </integrations>\n8:\n",
                        false,
                    ],
                ],
            ],
            'invalid attribute in integration' => [
                '<integrations>
                    <integration name="TestIntegration1" invalid="invalid">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [
                    [
                        "Element 'integration', attribute 'invalid': The attribute 'invalid' is not allowed.The xml " .
                        "was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                        "name=\"TestIntegration1\" invalid=\"invalid\">\n3:                        <email>" .
                        "test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                        "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                        "http://www.example.com/identity</identity_link_url>\n6:                    </integration>\n" .
                        "7:                </integrations>\n8:\n",
                        false,
                    ],
                ],
            ],
            'invalid attribute in email' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email invalid="invalid">test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [
                    [
                        "Element 'email', attribute 'invalid': The attribute 'invalid' is not allowed.The xml was: \n" .
                        "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                        "name=\"TestIntegration1\">\n3:                        <email invalid=\"invalid\">" .
                        "test-integration1@magento.com</email>\n4:                        <endpoint_url>" .
                        "http://endpoint.url</endpoint_url>\n5:                        <identity_link_url>" .
                        "http://www.example.com/identity</identity_link_url>\n6:                    </integration>\n" .
                        "7:                </integrations>\n8:\n",
                        false,
                    ],
                ],
            ],
            'invalid attribute in endpoint_url' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url invalid="invalid">http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                [
                    [
                        "Element 'endpoint_url', attribute 'invalid': The attribute 'invalid' is not allowed.The xml " .
                        "was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                        "name=\"TestIntegration1\">\n3:                        <email>test-integration1@magento.com" .
                        "</email>\n4:                        <endpoint_url invalid=\"invalid\">http://endpoint.url" .
                        "</endpoint_url>\n5:                        <identity_link_url>" .
                        "http://www.example.com/identity</identity_link_url>\n6:                    </integration>" .
                        "\n7:                </integrations>\n8:\n",
                        false,
                    ],
                ],
            ],
            'invalid attribute in identity_link_url' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url invalid="invalid">http://endpoint.url</identity_link_url>
                    </integration>
                </integrations>',
                [
                    [
                        "Element 'identity_link_url', attribute 'invalid': The attribute 'invalid' is not allowed." .
                        "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    " .
                        "<integration name=\"TestIntegration1\">\n3:                        " .
                        "<email>test-integration1@magento.com</email>\n4:                        " .
                        "<endpoint_url>http://endpoint.url</endpoint_url>\n5:                        " .
                        "<identity_link_url invalid=\"invalid\">http://endpoint.url</identity_link_url>" .
                        "\n6:                    </integration>\n7:                </integrations>\n8:\n",
                        false,
                    ],
                ],
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
                [
                    [
                        "Element 'integration': The attribute 'name' is required but missing.The xml was: \n" .
                        "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration>\n" .
                        "3:                        <email>test-integration1@magento.com</email>\n" .
                        "4:                        <endpoint_url>http://endpoint.url</endpoint_url>\n" .
                        "5:                        <identity_link_url>http://www.example.com/identity" .
                        "</identity_link_url>\n6:                    </integration>\n7:                " .
                        "</integrations>\n8:\n",
                        false,
                    ],
                ],
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
                    [
                        "/Element \'integration\', attribute \'name\': .*\'\'" .
                        " (is not a valid value|has a length of \'0\').*/",
                        true,
                    ],
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
                    [
                        "/Element \'email\': .*\'invalid\' is not (a valid value|accepted).*/",
                        true,
                    ],
                ],
            ]
        ];
    }
}
