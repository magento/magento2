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
                [
                    "Element 'integrations': Missing child element(s). Expected is ( integration ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<integrations/>\n2:\n"
                ],
            ],
            'empty integration' => [
                '<integrations>
                    <integration name="TestIntegration" />
                </integrations>',
                [
                    "Element 'integration': Missing child element(s). Expected is ( resources ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                    "name=\"TestIntegration\"/>\n3:                </integrations>\n4:\n"
                ],
            ],
            'empty resources' => [
                '<integrations>
                    <integration name="TestIntegration1">
                        <resources>
                        </resources>
                    </integration>
                </integrations>',
                [
                    "Element 'resources': Missing child element(s). Expected is ( resource ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <resources>\n4:                        " .
                    "</resources>\n5:                    </integration>\n6:                </integrations>\n7:\n"
                ],
            ],
            'irrelevant root node' => [
                '<integration name="TestIntegration"/>',
                [
                    "Element 'integration': No matching global declaration available for the validation root." .
                    "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integration name=\"TestIntegration\"/>\n2:\n"
                ],
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
                [
                    "Element 'invalid': This element is not expected. Expected is ( integration ).The xml was: \n" .
                    "3:                        <resources>\n4:                            <resource " .
                    "name=\"Magento_Customer::manage\"/>\n5:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n6:                        </resources>\n" .
                    "7:                    </integration>\n8:                    <invalid/>\n" .
                    "9:                </integrations>\n10:\n"
                ],
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
                [
                    "Element 'invalid': This element is not expected.The xml was: \n2:                    " .
                    "<integration name=\"TestIntegration1\">\n3:                        <resources>\n" .
                    "4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "6:                        </resources>\n7:                        <invalid/>\n" .
                    "8:                    </integration>\n9:                </integrations>\n10:\n"
                ],
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
                [
                    "Element 'invalid': This element is not expected. Expected is ( resource ).The xml was: \n" .
                    "1:<integrations>\n2:                    <integration name=\"TestIntegration1\">\n" .
                    "3:                        <resources>\n4:                            <resource " .
                    "name=\"Magento_Customer::manage\"/>\n5:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n6:                        <invalid/>\n" .
                    "7:                        </resources>\n8:                    </integration>\n" .
                    "9:                </integrations>\n10:\n"
                ],
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
                    "Element 'resource': Element content is not allowed, because the content type is a simple " .
                    "type definition.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n" .
                    "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                    "<resources>\n4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource name=\"Magento_Customer::online\">\n" .
                    "6:                                <invalid/>\n7:                            </resource>\n" .
                    "8:                        </resources>\n9:                    </integration>\n"
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
                [
                    "Element 'integrations', attribute 'invalid': The attribute 'invalid' is not allowed.The " .
                    "xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations invalid=\"invalid\">\n" .
                    "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                    "<resources>\n4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "6:                        </resources>\n7:                    </integration>\n" .
                    "8:                </integrations>\n9:\n"
                ],
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
                [
                    "Element 'integration', attribute 'invalid': The attribute 'invalid' is not allowed.The xml " .
                    "was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                    "name=\"TestIntegration1\" invalid=\"invalid\">\n3:                        <resources>\n" .
                    "4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "6:                        </resources>\n7:                    </integration>\n" .
                    "8:                </integrations>\n9:\n"
                ],
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
                [
                    "Element 'resources', attribute 'invalid': The attribute 'invalid' is not allowed.The xml " .
                    "was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <resources invalid=\"invalid\">\n" .
                    "4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "6:                        </resources>\n7:                    </integration>\n" .
                    "8:                </integrations>\n9:\n"
                ],
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
                [
                    "Element 'resource', attribute 'invalid': The attribute 'invalid' is not allowed.The " .
                    "xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <resources>\n" .
                    "4:                            <resource name=\"Magento_Customer::manage\" " .
                    "invalid=\"invalid\"/>\n5:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n6:                        </resources>\n" .
                    "7:                    </integration>\n8:                </integrations>\n9:\n"
                ],
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
                [
                    "Element 'integration': The attribute 'name' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration>\n" .
                    "3:                        <resources>\n4:                            <resource " .
                    "name=\"Magento_Customer::manage\"/>\n5:                            <resource " .
                    "name=\"Magento_Customer::online\"/>\n6:                        </resources>\n" .
                    "7:                    </integration>\n8:                </integrations>\n9:\n"
                ],
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
                    "this underruns the allowed minimum length of '2'.The xml was: \n0:<?xml version=\"1.0\"?>\n" .
                    "1:<integrations>\n2:                    <integration name=\"\">\n3:                        " .
                    "<resources>\n4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "6:                        </resources>\n7:                    </integration>\n" .
                    "8:                </integrations>\n9:\n"
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
                [
                    "Element 'resource': The attribute 'name' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<integrations>\n2:                    <integration " .
                    "name=\"TestIntegration1\">\n3:                        <resources>\n" .
                    "4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource/>\n6:                        </resources>\n" .
                    "7:                    </integration>\n8:                </integrations>\n9:\n"
                ],
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
                    "Element 'resource', attribute 'name': [facet 'pattern'] The value '' is not accepted by " .
                    "the pattern '.+_.+::.+'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n" .
                    "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                    "<resources>\n4:                            <resource name=\"Magento_Customer::manage\"/>\n" .
                    "5:                            <resource name=\"\"/>\n6:                        </resources>\n" .
                    "7:                    </integration>\n8:                </integrations>\n9:\n"
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
                    "Element 'resource', attribute 'name': [facet 'pattern'] The value 'customer_manage' is not " .
                    "accepted by the pattern '.+_.+::.+'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<integrations>\n" .
                    "2:                    <integration name=\"TestIntegration1\">\n3:                        " .
                    "<resources>\n4:                            <resource name=\"Magento_Customer::online\"/>\n" .
                    "5:                            <resource name=\"customer_manage\"/>\n6:                        " .
                    "</resources>\n7:                    </integration>\n8:                </integrations>\n9:\n"
                ],
            ]
        ];
    }
}
