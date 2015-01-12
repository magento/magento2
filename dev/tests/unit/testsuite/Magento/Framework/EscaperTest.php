<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * \Magento\Framework\Escaper test case
 */
class EscaperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper = null;

    protected function setUp()
    {
        $this->_escaper = new Escaper();
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeHtml
     * @dataProvider escapeHtmlDataProvider
     */
    public function testEscapeHtml($data, $expected, $allowedTags = null)
    {
        $actual = $this->_escaper->escapeHtml($data, $allowedTags);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function escapeHtmlDataProvider()
    {
        return [
            'array data' => [
                'data' => ['one', '<two>three</two>'],
                'expected' => ['one', '&lt;two&gt;three&lt;/two&gt;'],
                null,
            ],
            'string data conversion' => [
                'data' => '<two>three</two>',
                'expected' => '&lt;two&gt;three&lt;/two&gt;',
                null,
            ],
            'string data no conversion' => ['data' => 'one', 'expected' => 'one'],
            'string data with allowed tags' => [
                'data' => '<span><b>some text in tags</b></span>',
                'expected' => '<span><b>some text in tags</b></span>',
                'allowedTags' => ['span', 'b'],
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeUrl
     */
    public function testEscapeUrl()
    {
        $data = 'http://example.com/search?term=this+%26+that&view=list';
        $expected = 'http://example.com/search?term=this+%26+that&amp;view=list';
        $this->assertEquals($expected, $this->_escaper->escapeUrl($data));
        $this->assertEquals($expected, $this->_escaper->escapeUrl($expected));
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeJsQuote
     */
    public function testEscapeJsQuote()
    {
        $data = ["Don't do that.", 'lost_key' => "Can't do that."];
        $expected = ["Don\\'t do that.", "Can\\'t do that."];
        $this->assertEquals($expected, $this->_escaper->escapeJsQuote($data));
        $this->assertEquals($expected[0], $this->_escaper->escapeJsQuote($data[0]));
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeQuote
     */
    public function testEscapeQuote()
    {
        $data = "Text with 'single' and \"double\" quotes";
        $expected = [
            "Text with &#039;single&#039; and &quot;double&quot; quotes",
            "Text with \\&#039;single\\&#039; and \\&quot;double\\&quot; quotes",
        ];
        $this->assertEquals($expected[0], $this->_escaper->escapeQuote($data));
        $this->assertEquals($expected[1], $this->_escaper->escapeQuote($data, true));
    }
}
