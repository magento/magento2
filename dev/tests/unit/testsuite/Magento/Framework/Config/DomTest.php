<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return array(
            array(
                'ids.xml',
                'ids_new.xml',
                array(
                    '/root/node/subnode' => 'id',
                    '/root/other_node' => 'id',
                    '/root/other_node/child' => 'identifier'
                ),
                null,
                'ids_merged.xml'
            ),
            array('no_ids.xml', 'no_ids_new.xml', array(), null, 'no_ids_merged.xml'),
            array('ambiguous_one.xml', 'ambiguous_new_two.xml', array(), null, 'ambiguous_merged.xml'),
            array('namespaced.xml', 'namespaced_new.xml', array('/root/node' => 'id'), null, 'namespaced_merged.xml'),
            array('override_node.xml', 'override_node_new.xml', array(), null, 'override_node_merged.xml'),
            array('override_node_new.xml', 'override_node.xml', array(), null, 'override_node_merged.xml'),
            array('text_node.xml', 'text_node_new.xml', array(), null, 'text_node_merged.xml'),
            array(
                'recursive.xml',
                'recursive_new.xml',
                array('/root/(node|another_node)(/param)?' => 'name', '/root/node/param(/complex/item)+' => 'key'),
                null,
                'recursive_merged.xml'
            ),
            array(
                'recursive_deep.xml',
                'recursive_deep_new.xml',
                array('/root(/node)+' => 'name'),
                null,
                'recursive_deep_merged.xml'
            ),
            array(
                'types.xml',
                'types_new.xml',
                array('/root/item' => 'id', '/root/item/subitem' => 'id'),
                'xsi:type',
                'types_merged.xml'
            ),
            array(
                'attributes.xml',
                'attributes_new.xml',
                array('/root/item' => 'id', '/root/item/subitem' => 'id'),
                'xsi:type',
                'attributes_merged.xml'
            )
        );
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
        return array(
            'valid' => array('<root><node id="id1"/><node id="id2"/></root>', array()),
            'invalid' => array(
                '<root><node id="id1"/><unknown_node/></root>',
                array("Element 'unknown_node': This element is not expected. Expected is ( node ).\nLine: 1\n")
            )
        );
    }

    public function testValidateCustomErrorFormat()
    {
        $xml = '<root><unknown_node/></root>';
        $errorFormat = 'Error: `%message%`';
        $expectedErrors = array(
            "Error: `Element 'unknown_node': This element is not expected. Expected is ( node ).`"
        );
        $dom = new \Magento\Framework\Config\Dom($xml, array(), null, null, $errorFormat);
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
        $xml = '<root><unknown_node/></root>';
        $errorFormat = '%message%,%unknown%';
        $dom = new \Magento\Framework\Config\Dom($xml, array(), null, null, $errorFormat);
        $dom->validate(__DIR__ . '/_files/sample.xsd');
    }

    public function testValidateUnknownError()
    {
        $xml = '<root><node id="id1"/><node id="id2"/></root>';
        $schemaFile = __DIR__ . '/_files/sample.xsd';
        $dom = new \Magento\Framework\Config\Dom($xml);
        $domMock = $this->getMock('DOMDocument', array('schemaValidate'), array());
        $domMock->expects($this->once())
            ->method('schemaValidate')
            ->with($schemaFile)
            ->will($this->returnValue(false));
        $this->assertEquals(array('Unknown validation error'), $dom->validateDomDocument($domMock, $schemaFile));
    }
}
