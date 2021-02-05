<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

/**
 * Test for \Magento\Framework\Config\Dom class.
 */
class DomTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Config\ValidationStateInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $validationStateMock;

    protected function setUp(): void
    {
        $this->validationStateMock = $this->getMockForAbstractClass(
            \Magento\Framework\Config\ValidationStateInterface::class
        );
        $this->validationStateMock->method('isValidationRequired')
            ->willReturn(true);
    }

    /**
     * @param string $xmlFile
     * @param string $newXmlFile
     * @param array $ids
     * @param string|null $typeAttributeName
     * @param string $expectedXmlFile
     * @dataProvider mergeDataProvider
     */
    public function testMerge($xmlFile, $newXmlFile, $ids, $typeAttributeName, $expectedXmlFile)
    {
        $xml = file_get_contents(__DIR__ . "/_files/dom/{$xmlFile}");
        $newXml = file_get_contents(__DIR__ . "/_files/dom/{$newXmlFile}");
        $config = new \Magento\Framework\Config\Dom($xml, $this->validationStateMock, $ids, $typeAttributeName);
        $config->merge($newXml);
        $this->assertXmlStringEqualsXmlFile(__DIR__ . "/_files/dom/{$expectedXmlFile}", $config->getDom()->saveXML());
    }

    /**
     * @return array
     */
    public function mergeDataProvider()
    {
        // note differences of XML declaration in fixture files: sometimes encoding is specified, sometimes isn't
        return [
            [
                'ids.xml',
                'ids_new.xml',
                [
                    '/root/node/subnode' => 'id',
                    '/root/other_node' => 'id',
                    '/root/other_node/child' => 'identifier',
                ],
                null,
                'ids_merged.xml',
            ],
            ['no_ids.xml', 'no_ids_new.xml', [], null, 'no_ids_merged.xml'],
            ['ambiguous_one.xml', 'ambiguous_new_two.xml', [], null, 'ambiguous_merged.xml'],
            ['namespaced.xml', 'namespaced_new.xml', ['/root/node' => 'id'], null, 'namespaced_merged.xml'],
            ['override_node.xml', 'override_node_new.xml', [], null, 'override_node_merged.xml'],
            ['override_node_new.xml', 'override_node.xml', [], null, 'override_node_merged.xml'],
            ['text_node.xml', 'text_node_new.xml', [], null, 'text_node_merged.xml'],
            'text node replaced with cdata' => [
                'text_node_cdata.xml',
                'text_node_cdata_new.xml',
                [],
                null,
                'text_node_cdata_merged.xml'
            ],
            'cdata' => ['cdata.xml', 'cdata_new.xml', [], null, 'cdata_merged.xml'],
            'cdata with html' => ['cdata_html.xml', 'cdata_html_new.xml', [], null, 'cdata_html_merged.xml'],
            'cdata replaced with text node' => [
                'cdata_text.xml',
                'cdata_text_new.xml',
                [],
                null,
                'cdata_text_merged.xml'
            ],
            'big cdata' => ['big_cdata.xml', 'big_cdata_new.xml', [], null, 'big_cdata_merged.xml'],
            'big cdata with attribute' => [
                'big_cdata_attribute.xml',
                'big_cdata_attribute_new.xml',
                [],
                null,
                'big_cdata_attribute_merged.xml'
            ],
            'big cdata replaced with text' => [
                'big_cdata_text.xml',
                'big_cdata_text_new.xml',
                [],
                null,
                'big_cdata_text_merged.xml'
            ],
            [
                'recursive.xml',
                'recursive_new.xml',
                ['/root/(node|another_node)(/param)?' => 'name', '/root/node/param(/complex/item)+' => 'key'],
                null,
                'recursive_merged.xml',
            ],
            [
                'recursive_deep.xml',
                'recursive_deep_new.xml',
                ['/root(/node)+' => 'name'],
                null,
                'recursive_deep_merged.xml',
            ],
            [
                'types.xml',
                'types_new.xml',
                ['/root/item' => 'id', '/root/item/subitem' => 'id'],
                'xsi:type',
                'types_merged.xml',
            ],
            [
                'attributes.xml',
                'attributes_new.xml',
                ['/root/item' => 'id', '/root/item/subitem' => 'id'],
                'xsi:type',
                'attributes_merged.xml',
            ],
        ];
    }

    /**
     */
    public function testMergeException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('More than one node matching the query: /root/node/subnode');

        $xml = file_get_contents(__DIR__ . "/_files/dom/ambiguous_two.xml");
        $newXml = file_get_contents(__DIR__ . "/_files/dom/ambiguous_new_one.xml");
        $config = new \Magento\Framework\Config\Dom($xml, $this->validationStateMock);
        $config->merge($newXml);
    }

    /**
     * @param string $xml
     * @param array $expectedErrors
     * @dataProvider validateDataProvider
     */
    public function testValidate($xml, array $expectedErrors)
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $dom = new \Magento\Framework\Config\Dom($xml, $this->validationStateMock);
        $actualResult = $dom->validate(__DIR__ . '/_files/sample.xsd', $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'valid' => ['<root><node id="id1"/><node id="id2"/></root>', []],
            'invalid' => [
                '<root><node id="id1"/><unknown_node/></root>',
                ["Element 'unknown_node': This element is not expected. Expected is ( node ).\nLine: 1\n"],
            ],
        ];
    }

    public function testValidateCustomErrorFormat()
    {
        $xml = '<root><unknown_node/></root>';
        $errorFormat = 'Error: `%message%`';
        $expectedErrors = [
            "Error: `Element 'unknown_node': This element is not expected. Expected is ( node ).`",
        ];
        $dom = new \Magento\Framework\Config\Dom($xml, $this->validationStateMock, [], null, null, $errorFormat);
        $actualResult = $dom->validate(__DIR__ . '/_files/sample.xsd', $actualErrors);
        $this->assertFalse($actualResult);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    /**
     */
    public function testValidateCustomErrorFormatInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Error format \'%message%,%unknown%\' contains unsupported placeholders');

        $xml = '<root><unknown_node/></root>';
        $errorFormat = '%message%,%unknown%';
        $dom = new \Magento\Framework\Config\Dom($xml, $this->validationStateMock, [], null, null, $errorFormat);
        $dom->validate(__DIR__ . '/_files/sample.xsd');
    }

    public function testValidateUnknownError()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $xml = '<root><node id="id1"/><node id="id2"/></root>';
        $schemaFile = __DIR__ . '/_files/sample.xsd';
        $dom = new \Magento\Framework\Config\Dom($xml, $this->validationStateMock);
        $domMock = $this->createPartialMock(\DOMDocument::class, ['schemaValidate']);
        $domMock->expects($this->once())
            ->method('schemaValidate')
            ->with($schemaFile)
            ->willReturn(false);
        $this->assertEquals(
            ["Unknown validation error"],
            $dom->validateDomDocument($domMock, $schemaFile)
        );
    }

    /**
     */
    public function testValidateDomDocumentThrowsException()
    {
        $this->expectException(\Magento\Framework\Config\Dom\ValidationSchemaException::class);

        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $xml = '<root><node id="id1"/><node id="id2"/></root>';
        $schemaFile = __DIR__ . '/_files/sample.xsd';
        $dom = new \Magento\Framework\Config\Dom($xml, $this->validationStateMock);
        $domMock = $this->createPartialMock(\DOMDocument::class, ['schemaValidate']);
        $domMock->expects($this->once())
            ->method('schemaValidate')
            ->with($schemaFile)
            ->willThrowException(new \Exception());
        $dom->validateDomDocument($domMock, $schemaFile);
    }
}
