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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Tools\Migration\System\Configuration;

require_once realpath(__DIR__ . '/../../../../../../../../../')
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
        $this->assertEquals(array(), $this->_parser->parse(new \DOMDocument()));
    }

    public function testParseDomWithoutNodes()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<config>
</config>
XML;

        $expected = array();
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
        $expected = array(
            'comment' => $comment,
            'sections' => array(
                'some_section' => array(
                    'label' => array('#text' => 'Section Name'),
                    'tab' => array('#text' => 'test'),
                    'frontend_type' => array('#text' => 'text'),
                    'sort_order' => array('#text' => '140'),
                    'show_in_default' => array('#text' => '1'),
                    'show_in_website' => array('#text' => '1'),
                    'show_in_store' => array('#text' => '1'),
                    'resource' => array('#text' => 'Magento_Some::resource'),
                    '@attributes' => array('translate' => 'label')
                )
            )
        );
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $this->assertEquals($expected, $this->_parser->parse($dom));
    }
}
