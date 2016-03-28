<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use \Magento\Framework\Escaper;

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

    /**
     * @covers \Magento\Framework\Escaper::escapeXssInUrl
     * @param string $input
     * @param string $expected
     * @dataProvider escapeDataProvider
     */
    public function testEscapeXssInUrl($input, $expected)
    {
        $this->assertEquals($expected, $this->_escaper->escapeXssInUrl($input));
    }

    /**
     * Get escape variations
     * @return array
     */
    public function escapeDataProvider()
    {
        return [
            [
                'javascript%3Aalert%28String.fromCharCode%280x78%29%2BString.'
                . 'fromCharCode%280x73%29%2BString.fromCharCode%280x73%29%29',
                ':alert%28String.fromCharCode%280x78%29%2BString.'
                . 'fromCharCode%280x73%29%2BString.fromCharCode%280x73%29%29'
            ],
            [
                'http://test.com/?redirect=JAVASCRIPT:alert%281%29',
                'http://test.com/?redirect=:alert%281%29',
            ],
            [
                'http://test.com/?redirect=javascript:alert%281%29',
                'http://test.com/?redirect=:alert%281%29',
            ],
            [
                'http://test.com/?redirect=JavaScript:alert%281%29',
                'http://test.com/?redirect=:alert%281%29',
            ],
            [
                'http://test.com/?redirect=javascript:alert(1)',
                'http://test.com/?redirect=:alert(1)',
            ],
            [
                'http://test.com/?redirect=javascript:alert(1)&test=1',
                'http://test.com/?redirect=:alert(1)&amp;test=1',
            ],
            [
                'http://test.com/?redirect=\x6A\x61\x76\x61\x73\x63\x72\x69\x70\x74:alert(1)',
                'http://test.com/?redirect=:alert(1)',
            ],
            [
                'http://test.com/?redirect=vbscript:alert(1)',
                'http://test.com/?redirect=:alert(1)',
            ],
            [
                'http://test.com/?redirect=data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg',
                'http://test.com/?redirect=:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg',
            ],
            [
                'http://test.com/?redirect=data%3Atext%2Fhtml%3Bbase64%2CPHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg',
                'http://test.com/?redirect=:text%2Fhtml%3Bbase64%2CPHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg',
            ],
            [
                'http://test.com/?redirect=\x64\x61\x74\x61\x3a\x74\x65\x78\x74x2cCPHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg',
                'http://test.com/?redirect=:\x74\x65\x78\x74x2cCPHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg',
            ],
        ];
    }
}
