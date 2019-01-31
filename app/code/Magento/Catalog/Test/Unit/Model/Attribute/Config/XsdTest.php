<?php
/**
 * Test for validation rules implemented by XSD schema for catalog attributes configuration
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Attribute\Config;

class XsdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->_schemaFile = $urnResolver->getRealPath('urn:magento:module:Magento_Catalog:etc/catalog_attributes.xsd');
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, $validationStateMock, [], null, null, '%message%');
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
                ["Element 'config': Missing child element(s). Expected is ( group )."],
            ],
            'irrelevant root node' => [
                '<attribute name="attr"/>',
                ["Element 'attribute': No matching global declaration available for the validation root."],
            ],
            'empty node "group"' => [
                '<config><group name="test"/></config>',
                ["Element 'group': Missing child element(s). Expected is ( attribute )."],
            ],
            'node "group" without attribute "name"' => [
                '<config><group><attribute name="attr"/></group></config>',
                ["Element 'group': The attribute 'name' is required but missing."],
            ],
            'node "group" with invalid attribute' => [
                '<config><group name="test" invalid="true"><attribute name="attr"/></group></config>',
                ["Element 'group', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'node "attribute" with value' => [
                '<config><group name="test"><attribute name="attr">Invalid</attribute></group></config>',
                ["Element 'attribute': Character content is not allowed, because the content type is empty."],
            ],
            'node "attribute" with children' => [
                '<config><group name="test"><attribute name="attr"><invalid/></attribute></group></config>',
                ["Element 'attribute': Element content is not allowed, because the content type is empty."],
            ],
            'node "attribute" without attribute "name"' => [
                '<config><group name="test"><attribute/></group></config>',
                ["Element 'attribute': The attribute 'name' is required but missing."],
            ],
            'node "attribute" with invalid attribute' => [
                '<config><group name="test"><attribute name="attr" invalid="true"/></group></config>',
                ["Element 'attribute', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ]
        ];
    }
}
