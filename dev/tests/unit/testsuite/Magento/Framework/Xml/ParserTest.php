<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Xml;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Xml\Parser */
    protected $parser;

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
}
