<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

class DomTest extends \PHPUnit_Framework_TestCase
{
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
        $config = new \Magento\Framework\Config\Dom($xml, $ids, $typeAttributeName);
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
                    '/root/other_node/child' => 'identifier'
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
            [
                'recursive.xml',
                'recursive_new.xml',
                ['/root/(node|another_node)(/param)?' => 'name', '/root/node/param(/complex/item)+' => 'key'],
                null,
                'recursive_merged.xml'
            ],
            [
                'recursive_deep.xml',
                'recursive_deep_new.xml',
                ['/root(/node)+' => 'name'],
                null,
                'recursive_deep_merged.xml'
            ],
            [
                'types.xml',
                'types_new.xml',
                ['/root/item' => 'id', '/root/item/subitem' => 'id'],
                'xsi:type',
                'types_merged.xml'
            ],
            [
                'attributes.xml',
                'attributes_new.xml',
                ['/root/item' => 'id', '/root/item/subitem' => 'id'],
                'xsi:type',
                'attributes_merged.xml'
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage More than one node matching the query: /root/node/subnode
     */
    public function testMergeException()
    {
        $xml = file_get_contents(__DIR__ . "/_files/dom/ambiguous_two.xml");
        $newXml = file_get_contents(__DIR__ . "/_files/dom/ambiguous_new_one.xml");
        $config = new \Magento\Framework\Config\Dom($xml);
        $config->merge($newXml);
    }

    /**
     * @expectedException \Magento\Framework\Config\Dom\ValidationException
     * @expectedExceptionMessage Opening and ending tag mismatch: root line 7 and xroot
     */
    public function testLoadXMLMalformedXmlException()
    {
	$xml = file_get_contents(__DIR__ . "/_files/dom/malformed1.xml");
	$config = new \Magento\Framework\Config\Dom($xml);
    }

    /**
     * @expectedException \Magento\Framework\Config\Dom\ValidationException
     * @expectedExceptionMessage Start tag expected, '<' not found
     */
    public function testLoadXMLEmptyXmlException()
    {
	$xml = file_get_contents(__DIR__ . "/_files/dom/malformed2.xml");
	$config = new \Magento\Framework\Config\Dom($xml);
    }

    /**
     * @param string $xml
     * @param array $expectedErrors
     * @dataProvider validateDataProvider
     */
    public function testValidate($xml, array $expectedErrors)
    {
        $dom = new \Magento\Framework\Config\Dom($xml);
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
		'<root><node id="id1"/><unknown_node1/></root>',
		["Element 'unknown_node1': This element is not expected. Expected is ( node ).\nLine: 1\n"],
            ]
        ];
    }

    public function testValidateCustomErrorFormat()
    {
	$xml = '<root><unknown_node2/></root>';
        $errorFormat = 'Error: `%message%`';
        $expectedErrors = [
	    "Error: `Element 'unknown_node2': This element is not expected. Expected is ( node ).`",
        ];
        $dom = new \Magento\Framework\Config\Dom($xml, [], null, null, $errorFormat);
        $actualResult = $dom->validate(__DIR__ . '/_files/sample.xsd', $actualErrors);
        $this->assertFalse($actualResult);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error format '%message%,%unknown%' contains unsupported placeholders
     */
    public function testValidateCustomErrorFormatInvalid()
    {
	$xml = '<root><unknown_node3/></root>';
        $errorFormat = '%message%,%unknown%';
        $dom = new \Magento\Framework\Config\Dom($xml, [], null, null, $errorFormat);
        $dom->validate(__DIR__ . '/_files/sample.xsd');
    }

    /**
     * This test method belongs normally into XML/ParserTest but here it is very useful to show the
     * usage of libxml_clear_errors() in method validateDomDocument
     */
    public function testValidateUnknownError()
    {
        $schemaFile = __DIR__ . '/_files/sample.xsd';
        $domMock = $this->getMock('DOMDocument', ['schemaValidate'], []);
        $domMock->expects($this->once())
            ->method('schemaValidate')
            ->with($schemaFile)
            ->will($this->returnValue(false));
	$this->assertEquals(
	    ['Unknown validation error'],
	    \Magento\Framework\Xml\Parser::validateDomDocument($domMock, $schemaFile)
	);
    }
}
