<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Migration\System\Configuration;

require_once realpath(__DIR__ . '/../../../../../../../../')
    . '/tools/Magento/Tools/Migration/System/Configuration/Parser.php';

/**
 * Tools_Migration_System_Configuration_Parser test case
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Parser
     */
    protected $_parser;

    protected function setUp()
    {
        $this->_parser = new \Magento\Tools\Migration\System\Configuration\Parser();
    }

    protected function tearDown()
    {
        $this->_parser = null;
    }

    public function testParseEmptyDom()
    {
        $this->assertEquals([], $this->_parser->parse(new \DOMDocument()));
    }

    public function testParseDomWithoutNodes()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<config>
</config>
XML;

        $expected = [];
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $this->assertEquals($expected, $this->_parser->parse($dom));
    }

    public function testParseDomNodes()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<!--
/**
 * some comment
 */
-->
<config>
    <sections>
        <some_section translate="label">
            <label>Section Name</label>
            <tab>test</tab>
            <frontend_type>text</frontend_type>
            <sort_order>140</sort_order>
            <show_in_default>1</show_in_default>
            <show_in_website>1</show_in_website>
            <show_in_store>1</show_in_store>
            <resource>Magento_Some::resource</resource>
        </some_section>
    </sections>
</config>
XML;

        $comment = <<<XMLCOMMENT

/**
 * some comment
 */

XMLCOMMENT;
        $expected = [
            'comment' => $comment,
            'sections' => [
                'some_section' => [
                    'label' => ['#text' => 'Section Name'],
                    'tab' => ['#text' => 'test'],
                    'frontend_type' => ['#text' => 'text'],
                    'sort_order' => ['#text' => '140'],
                    'show_in_default' => ['#text' => '1'],
                    'show_in_website' => ['#text' => '1'],
                    'show_in_store' => ['#text' => '1'],
                    'resource' => ['#text' => 'Magento_Some::resource'],
                    '@attributes' => ['translate' => 'label'],
                ],
            ],
        ];
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $this->assertEquals($expected, $this->_parser->parse($dom));
    }
}
