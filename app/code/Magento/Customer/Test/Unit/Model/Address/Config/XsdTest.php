<?php
/**
 * Test for validation rules implemented by XSD schema for customer address format configuration
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address\Config;

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
        $this->_schemaFile = $urnResolver->getRealPath('urn:magento:module:Magento_Customer:etc/address_formats.xsd');
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
            'valid' => ['<config><format code="code" title="title" /></config>', []],
            'valid with optional attributes' => [
                '<config><format code="code" title="title" renderer="Some_Renderer" escapeHtml="false" /></config>',
                [],
            ],
            'empty root node' => [
                '<config/>',
                ["Element 'config': Missing child element(s). Expected is ( format )."],
            ],
            'irrelevant root node' => [
                '<attribute name="attr"/>',
                ["Element 'attribute': No matching global declaration available for the validation root."],
            ],
            'irrelevant node' => [
                '<config><format code="code" title="title" /><invalid /></config>',
                ["Element 'invalid': This element is not expected. Expected is ( format )."],
            ],
            'non empty node "format"' => [
                '<config><format code="code" title="title"><invalid /></format></config>',
                ["Element 'format': Element content is not allowed, because the content type is empty."],
            ],
            'node "format" without attribute "code"' => [
                '<config><format title="title" /></config>',
                ["Element 'format': The attribute 'code' is required but missing."],
            ],
            'node "format" without attribute "title"' => [
                '<config><format code="code" /></config>',
                ["Element 'format': The attribute 'title' is required but missing."],
            ],
            'node "format" with invalid attribute' => [
                '<config><format code="code" title="title" invalid="invalid" /></config>',
                ["Element 'format', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'attribute "escapeHtml" with invalid type' => [
                '<config><format code="code" title="title" escapeHtml="invalid" /></config>',
                [
                    "Element 'format', attribute 'escapeHtml': 'invalid' is not a valid value of the atomic type" .
                    " 'xs:boolean'."
                ],
            ]
        ];
    }
}
