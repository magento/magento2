<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Xml;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Xml\Parser */
    protected $parser;

    /**
     * A sample invalid XML
     *
     * @var string
     */
    private static $sampleXml = '<?xml version="1.0"?><config></onfig>';

    protected function setUp()
    {
        $this->parser = new \Magento\Framework\Xml\Parser();
    }

    public function testGetXml()
    {
        $this->assertEquals(
            ['data' => [
                'nodes' => [
                    'text' => ' some text ',
                    'trim_spaces' => '',
                    'cdata' => '  Some data here <strong>html</strong> tags are <i>allowed</i>  ',
                    'zero' => '0',
                    'null' => null,
                ]
            ]],
            $this->parser->load(__DIR__ . '/_files/data.xml')->xmlToArray()
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage DOMDocument::loadXML(): Opening and ending tag mismatch
     */
    public function testInvalidXml()
    {
        $this->parser->initErrorHandler();
        $this->parser->loadXML(self::$sampleXml);
    }
}
