<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for validation rules implemented by XSD schemas for email templates configuration
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * Test validation rules implemented by XSD schema for merged configs
     *
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider mergedXmlDataProvider
     */
    public function testMergedXml($fixtureXml, array $expectedErrors)
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $schemaFile = $urnResolver->getRealPath('urn:magento:module:Magento_Email:etc/email_templates.xsd');
        $this->_testXmlAgainstXsd($fixtureXml, $schemaFile, $expectedErrors);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function mergedXmlDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'valid' => [
                '<config><template id="test" label="Test" file="test.txt" type="text" module="Module" area="frontend"/></config>',
                [],
            ],
            'empty root node' => [
                '<config/>',
                [
                    "Element 'config': Missing child element(s). Expected is ( template ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config/>\n2:\n"
                ],
            ],
            'irrelevant root node' => [
                '<template id="test" label="Test" file="test.txt" type="text" module="Module" area="frontend"/>',
                [
                    "Element 'template': No matching global declaration available for the validation root." .
                    "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<template id=\"test\" label=\"Test\" " .
                    "file=\"test.txt\" type=\"text\" module=\"Module\" area=\"frontend\"/>\n2:\n"
                ],
            ],
            'invalid node' => [
                '<config><invalid/></config>',
                [
                    "Element 'invalid': This element is not expected. Expected is ( template ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><invalid/></config>\n2:\n"
                ],
            ],
            'node "template" with value' => [
                '<config>
                    <template id="test" label="Test" file="test.txt" type="text" module="Module" area="frontend">invalid</template>
                </config>',
                [
                    "Element 'template': Character content is not allowed, because the content type is empty." .
                    "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <template " .
                    "id=\"test\" label=\"Test\" file=\"test.txt\" type=\"text\" module=\"Module\" " .
                    "area=\"frontend\">invalid</template>\n3:                </config>\n4:\n"
                ],
            ],
            'node "template" with children' => [
                '<config>
                    <template id="test" label="Test" file="test.txt" type="text" module="Module" area="frontend"><invalid/></template>
                </config>',
                [
                    "Element 'template': Element content is not allowed, because the content type is empty.The xml " .
                    "was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <template id=\"test\" " .
                    "label=\"Test\" file=\"test.txt\" type=\"text\" module=\"Module\" area=\"frontend\"><invalid/>" .
                    "</template>\n3:                </config>\n4:\n"
                ],
            ],
            'node "template" without attribute "id"' => [
                '<config><template label="Test" file="test.txt" type="text" module="Module" area="frontend"/></config>',
                [
                    "Element 'template': The attribute 'id' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><template label=\"Test\" file=\"test.txt\" type=\"text\" " .
                    "module=\"Module\" area=\"frontend\"/></config>\n2:\n"
                ],
            ],
            'node "template" without attribute "label"' => [
                '<config><template id="test" file="test.txt" type="text" module="Module" area="frontend"/></config>',
                [
                    "Element 'template': The attribute 'label' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><template id=\"test\" file=\"test.txt\" type=\"text\" " .
                    "module=\"Module\" area=\"frontend\"/></config>\n2:\n"
                ],
            ],
            'node "template" without attribute "file"' => [
                '<config><template id="test" label="Test" type="text" module="Module" area="frontend"/></config>',
                [
                    "Element 'template': The attribute 'file' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><template id=\"test\" label=\"Test\" type=\"text\" " .
                    "module=\"Module\" area=\"frontend\"/></config>\n2:\n"
                ],
            ],
            'node "template" without attribute "type"' => [
                '<config><template id="test" label="Test" file="test.txt" module="Module" area="frontend"/></config>',
                [
                    "Element 'template': The attribute 'type' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><template id=\"test\" label=\"Test\" file=\"test.txt\" " .
                    "module=\"Module\" area=\"frontend\"/></config>\n2:\n"
                ],
            ],
            'node "template" with invalid attribute "type"' => [
                '<config><template id="test" label="Test" file="test.txt" type="invalid" module="Module" area="frontend"/></config>',
                [
                    "Element 'template', attribute 'type': [facet 'enumeration'] The value 'invalid' is not an " .
                    "element of the set {'html', 'text'}.The xml was: \n0:<?xml version=\"1.0\"?>\n" .
                    "1:<config><template id=\"test\" label=\"Test\" file=\"test.txt\" type=\"invalid\" " .
                    "module=\"Module\" area=\"frontend\"/></config>\n2:\n"
                ],
            ],
            'node "template" without attribute "area"' => [
                '<config><template id="test" label="Test" file="test.txt" type="text" module="Module"/></config>',
                [
                    "Element 'template': The attribute 'area' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><template id=\"test\" label=\"Test\" file=\"test.txt\" " .
                    "type=\"text\" module=\"Module\"/></config>\n2:\n"
                ],
            ],
            'node "template" with invalid attribute "area"' => [
                '<config><template id="test" label="Test" file="test.txt" type="text" module="Module" area="invalid"/></config>',
                [
                    "Element 'template', attribute 'area': 'invalid' is not a valid value of the atomic type " .
                    "'areaType'.The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><template id=\"test\" " .
                    "label=\"Test\" file=\"test.txt\" type=\"text\" module=\"Module\" area=\"invalid\"/>" .
                    "</config>\n2:\n",
                ],
            ],
            'node "template" with unknown attribute' => [
                '<config>
                    <template id="test" label="Test" file="test.txt" type="text" module="Module" area="frontend" unknown="true"/>
                </config>',
                [
                    "Element 'template', attribute 'unknown': The attribute 'unknown' is not allowed.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config>\n2:                    <template id=\"test\" " .
                    "label=\"Test\" file=\"test.txt\" type=\"text\" module=\"Module\" area=\"frontend\" " .
                    "unknown=\"true\"/>\n3:                </config>\n4:\n"
                ],
            ]
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Test that XSD schema validates fixture XML contents producing expected results
     *
     * @param string $fixtureXml
     * @param string $schemaFile
     * @param array $expectedErrors
     */
    protected function _testXmlAgainstXsd($fixtureXml, $schemaFile, array $expectedErrors)
    {
        $validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $dom = new Dom($fixtureXml, $validationStateMock, [], null, null, '%message%');
        $actualResult = $dom->validate($schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult);
        foreach ($expectedErrors as $error) {
            $this->assertContains($error, $actualErrors);
        }
    }
}
