<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Escaper;

/**
 * \Magento\Framework\Escaper test case
 */
class EscaperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper = null;

    /**
     * @var \Magento\Framework\ZendEscaper
     */
    private $zendEscaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->_escaper = new Escaper();
        $this->zendEscaper = new \Magento\Framework\ZendEscaper();
        $this->loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->setBackwardCompatibleProperty($this->_escaper, 'escaper', $this->zendEscaper);
        $objectManagerHelper->setBackwardCompatibleProperty($this->_escaper, 'logger', $this->loggerMock);
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeHtml
     * @dataProvider escapeHtmlDataProvider
     */
    public function testEscapeHtml($data, $expected, $allowedTags = [])
    {
        $actual = $this->_escaper->escapeHtml($data, $allowedTags);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeHtml
     * @dataProvider escapeHtmlInvalidDataProvider
     */
    public function testEscapeHtmlWithInvalidData($data, $expected, $allowedTags = [])
    {
        $this->loggerMock->expects($this->once())
            ->method('critical');
        $actual = $this->_escaper->escapeHtml($data, $allowedTags);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function escapeHtmlDataProvider()
    {
        return [
            'array -> [text with no tags, text with no allowed tags]' => [
                'data' => ['one', '<two>three</two>'],
                'expected' => ['one', '&lt;two&gt;three&lt;/two&gt;'],
            ],
            'text with special characters' => [
                'data' => '&<>"\'&amp;&lt;&gt;&quot;&#039;&#9;',
                'expected' => '&amp;&lt;&gt;&quot;&#039;&amp;&lt;&gt;&quot;&#039;&#9;'
            ],
            'text with multiple allowed tags, includes self closing tag' => [
                'data' => '<span>some text in tags<br /></span>',
                'expected' => '<span>some text in tags<br></span>',
                'allowedTags' => ['span', 'br'],
            ],
            'text with multiple allowed tags and allowed attribute in double quotes' => [
                'data' => 'Only <span id="sku_max_allowed"><b>2</b></span> in stock',
                'expected' => 'Only <span id="sku_max_allowed"><b>2</b></span> in stock',
                'allowedTags' => ['span', 'b'],
            ],
            'text with multiple allowed tags and allowed attribute in single quotes' => [
                'data' => 'Only <span id=\'sku_max_allowed\'><b>2</b></span> in stock',
                'expected' => 'Only <span id="sku_max_allowed"><b>2</b></span> in stock',
                'allowedTags' => ['span', 'b'],
            ],
            'text with multiple allowed tags with allowed attribute' => [
                'data' => 'Only registered users can write reviews. Please <a href="%1">Sign in</a> or <a href="%2">'
                    . 'create an account</a>',
                'expected' => 'Only registered users can write reviews. Please <a href="%1">Sign in</a> or '
                    . '<a href="%2">create an account</a>',
                'allowedTags' => ['a'],
            ],
            'text with not allowed attribute in single quotes' => [
                'data' => 'Only <span type=\'1\'><b>2</b></span> in stock',
                'expected' => 'Only <span><b>2</b></span> in stock',
                'allowedTags' => ['span', 'b'],
            ],
            'text with allowed and not allowed tags' => [
                'data' => 'Only registered users can write reviews. Please <a href="%1">Sign in<span>three</span></a> '
                    . 'or <a href="%2"><span id="action">create an account</span></a>',
                'expected' => 'Only registered users can write reviews. Please <a href="%1">Sign inthree</a> or '
                    . '<a href="%2">create an account</a>',
                'allowedTags' => ['a'],
            ],
            'text with allowed and not allowed tags, with allowed and not allowed attributes' => [
                'data' => 'Some test <span>text in span tag</span> <strong>text in strong tag</strong> '
                    . '<a type="some-type" href="http://domain.com/" onclick="alert(1)">Click here</a><script>alert(1)'
                    . '</script>',
                'expected' => 'Some test <span>text in span tag</span> text in strong tag <a href="http://domain.com/">'
                    . 'Click here</a>alert(1)',
                'allowedTags' => ['a', 'span'],
            ],
            'text with html comment' => [
                'data' => 'Only <span><b>2</b></span> in stock <!-- HTML COMMENT -->',
                'expected' => 'Only <span><b>2</b></span> in stock <!-- HTML COMMENT -->',
                'allowedTags' => ['span', 'b'],
            ],
            'text with non ascii characters' => [
                'data' => ['абвгд', 'مثال'],
                'expected' => ['абвгд', 'مثال'],
                'allowedTags' => [],
            ],
            'html and body tags' => [
                'data' => '<html><body><span>String</span></body></html>',
                'expected' => '',
                'allowedTags' => ['span'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function escapeHtmlInvalidDataProvider()
    {
        return [
            'text with allowed script tag' => [
                'data' => '<span><script>some text in tags</script></span>',
                'expected' => '',
                'allowedTags' => ['span', 'script'],
            ],
            'text with invalid html' => [
                'data' => '<spa>n id="id1">Some string</span>',
                'expected' => '',
                'allowedTags' => ['span'],
            ],
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
