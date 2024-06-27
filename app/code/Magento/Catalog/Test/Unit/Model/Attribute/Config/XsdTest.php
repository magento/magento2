<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->_schemaFile = $urnResolver->getRealPath('urn:magento:module:Magento_Catalog:etc/catalog_attributes.xsd');
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
        $dom = new Dom($fixtureXml, $validationStateMock, [], null, null, '%message%');
        $actualResult = $dom->validate($this->_schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    /**
     * @return array
     */
    public function exemplarXmlDataProvider()
    {
        return [
            'valid' => ['<config><group name="test"><attribute name="attr"/></group></config>', []],
            'empty root node' => [
                '<config/>',
                [
                    "Element 'config': Missing child element(s). Expected is ( group ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config/>\n2:\n"
                ],
            ],
            'irrelevant root node' => [
                '<attribute name="attr"/>',
                [
                    "Element 'attribute': No matching global declaration available for the validation root.The " .
                    "xml was: \n0:<?xml version=\"1.0\"?>\n1:<attribute name=\"attr\"/>\n2:\n"
                ],
            ],
            'empty node "group"' => [
                '<config><group name="test"/></config>',
                [
                    "Element 'group': Missing child element(s). Expected is ( attribute ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><group name=\"test\"/></config>\n2:\n"
                ],
            ],
            'node "group" without attribute "name"' => [
                '<config><group><attribute name="attr"/></group></config>',
                [
                    "Element 'group': The attribute 'name' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><group><attribute name=\"attr\"/></group></config>\n2:\n"
                ],
            ],
            'node "group" with invalid attribute' => [
                '<config><group name="test" invalid="true"><attribute name="attr"/></group></config>',
                [
                    "Element 'group', attribute 'invalid': The attribute 'invalid' is not allowed.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><group name=\"test\" invalid=\"true\">" .
                    "<attribute name=\"attr\"/></group></config>\n2:\n"
                ],
            ],
            'node "attribute" with value' => [
                '<config><group name="test"><attribute name="attr">Invalid</attribute></group></config>',
                [
                    "Element 'attribute': Character content is not allowed, because the content type is empty." .
                    "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><group name=\"test\">" .
                    "<attribute name=\"attr\">Invalid</attribute></group></config>\n2:\n"
                ],
            ],
            'node "attribute" with children' => [
                '<config><group name="test"><attribute name="attr"><invalid/></attribute></group></config>',
                [
                    "Element 'attribute': Element content is not allowed, because the content type is empty." .
                    "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><group name=\"test\">" .
                    "<attribute name=\"attr\"><invalid/></attribute></group></config>\n2:\n"
                ],
            ],
            'node "attribute" without attribute "name"' => [
                '<config><group name="test"><attribute/></group></config>',
                [
                    "Element 'attribute': The attribute 'name' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config><group name=\"test\"><attribute/></group></config>\n2:\n"
                ],
            ],
            'node "attribute" with invalid attribute' => [
                '<config><group name="test"><attribute name="attr" invalid="true"/></group></config>',
                [
                    "Element 'attribute', attribute 'invalid': The attribute 'invalid' is not allowed.The xml " .
                    "was: \n0:<?xml version=\"1.0\"?>\n1:<config><group name=\"test\"><attribute " .
                    "name=\"attr\" invalid=\"true\"/></group></config>\n2:\n"
                ],
            ]
        ];
    }
}
