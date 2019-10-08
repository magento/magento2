<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Translate\Inline;

/**
 * \Magento\Framework\Escaper test case
 */
class EscaperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\ZendEscaper
     */
    private $zendEscaper;

    /**
     * @var Inline
     */
    private $translateInline;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->escaper = new Escaper();
        $this->zendEscaper = new \Magento\Framework\ZendEscaper();
        $this->translateInline = $objectManagerHelper->getObject(Inline::class);
        $this->loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
        $objectManagerHelper->setBackwardCompatibleProperty($this->escaper, 'escaper', $this->zendEscaper);
        $objectManagerHelper->setBackwardCompatibleProperty($this->escaper, 'logger', $this->loggerMock);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->escaper,
            'translateInline',
            $this->translateInline
        );
    }

    /**
     * Convert a unicode codepoint to a literal UTF-8 character
     *
     * @param int $codepoint Unicode codepoint in hex notation
     * @return string UTF-8 literal string
     * @throws \Exception
     */
    protected function codepointToUtf8($codepoint)
    {
        if ($codepoint < 0x80) {
            return chr($codepoint);
        }
        if ($codepoint < 0x800) {
            return chr($codepoint >> 6 & 0x3f | 0xc0)
                . chr($codepoint & 0x3f | 0x80);
        }
        if ($codepoint < 0x10000) {
            return chr($codepoint >> 12 & 0x0f | 0xe0)
                . chr($codepoint >> 6 & 0x3f | 0x80)
                . chr($codepoint & 0x3f | 0x80);
        }
        if ($codepoint < 0x110000) {
            return chr($codepoint >> 18 & 0x07 | 0xf0)
                . chr($codepoint >> 12 & 0x3f | 0x80)
                . chr($codepoint >> 6 & 0x3f | 0x80)
                . chr($codepoint & 0x3f | 0x80);
        }
        throw new \Exception('Codepoint requested outside of unicode range');
    }

    public function testEscapeJsEscapesOwaspRecommendedRanges()
    {
        // Exceptions to escaping ranges
        $immune = [',', '.', '_'];
        for ($chr = 0; $chr < 0xFF; $chr++) {
            if (($chr >= 0x30 && $chr <= 0x39)
                || ($chr >= 0x41 && $chr <= 0x5A)
                || ($chr >= 0x61 && $chr <= 0x7A)
            ) {
                $literal = $this->codepointToUtf8($chr);
                $this->assertEquals($literal, $this->escaper->escapeJs($literal));
            } else {
                $literal = $this->codepointToUtf8($chr);
                if (in_array($literal, $immune)) {
                    $this->assertEquals($literal, $this->escaper->escapeJs($literal));
                } else {
                    $this->assertNotEquals(
                        $literal,
                        $this->escaper->escapeJs($literal),
                        $literal . ' should be escaped!'
                    );
                }
            }
        }
    }

    /**
     * @param string $data
     * @param string $expected
     * @dataProvider escapeJsDataProvider
     */
    public function testEscapeJs($data, $expected)
    {
        $this->assertEquals($expected, $this->escaper->escapeJs($data));
    }

    /**
     * @return array
     */
    public function escapeJsDataProvider()
    {
        return [
            'zero length string' => ['', ''],
            'only digits' => ['123', '123'],
            '<' => ['<', '\u003C'],
            '>' => ['>', '\\u003E'],
            '\'' => ['\'', '\\u0027'],
            '"' => ['"', '\\u0022'],
            '&' => ['&', '\\u0026'],
            'Characters beyond ASCII value 255 to unicode escape' => ['Ā', '\\u0100'],
            'Characters beyond Unicode BMP to unicode escape' => ["\xF0\x90\x80\x80", '\\uD800DC00'],
            /* Immune chars excluded */
            ',' => [',', ','],
            '.' => ['.', '.'],
            '_' => ['_', '_'],
            /* Basic alnums exluded */
            'a' => ['a', 'a'],
            'A' => ['A', 'A'],
            'z' => ['z', 'z'],
            'Z' => ['Z', 'Z'],
            '0' => ['0', '0'],
            '9' => ['9', '9'],
            /* Basic control characters and null */
            "\r" => ["\r", '\\u000D'],
            "\n" => ["\n", '\\u000A'],
            "\t" => ["\t", '\\u0009'],
            "\0" => ["\0", '\\u0000'],
            'Encode spaces for quoteless attribute protection' => [' ', '\\u0020'],
        ];
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeHtml
     * @dataProvider escapeHtmlDataProvider
     */
    public function testEscapeHtml($data, $expected, $allowedTags = [])
    {
        $actual = $this->escaper->escapeHtml($data, $allowedTags);
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
        $actual = $this->escaper->escapeHtml($data, $allowedTags);
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
            'text with special characters and allowed tag' => [
                'data' => '&<br/>"\'&amp;&lt;&gt;&quot;&#039;&#9;',
                'expected' => '&amp;<br>&quot;&#039;&amp;&lt;&gt;&quot;&#039;&#9;',
                'allowedTags' => ['br'],
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
                'expected' => 'Only <span><b>2</b></span> in stock ',
                'allowedTags' => ['span', 'b'],
            ],
            'text with multi-line html comment' => [
                'data' => "Only <span><b>2</b></span> in stock <!-- --!\n\n><img src=#>-->",
                'expected' => 'Only <span><b>2</b></span> in stock ',
                'allowedTags' => ['span', 'b'],
            ],
            'text with non ascii characters' => [
                'data' => ['абвгд', 'مثال', '幸福'],
                'expected' => ['абвгд', 'مثال', '幸福'],
                'allowedTags' => [],
            ],
            'html and body tags' => [
                'data' => '<html><body><span>String</span></body></html>',
                'expected' => '<span>String</span>',
                'allowedTags' => ['span'],
            ],
            'invalid tag' => [
                'data' => '<some tag> some text',
                'expected' => ' some text',
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
                'expected' => '<span>some text in tags</span>',
                'allowedTags' => ['span', 'script'],
            ],
            'text with invalid html' => [
                'data' => '<spa>n id="id1">Some string</span>',
                'expected' => 'n id=&quot;id1&quot;&gt;Some string',
                'allowedTags' => ['span'],
            ],
        ];
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeUrl
     *
     * @param string $data
     * @param string $expected
     * @return void
     *
     * @dataProvider escapeUrlDataProvider
     */
    public function testEscapeUrl(string $data, string $expected): void
    {
        $this->assertEquals($expected, $this->escaper->escapeUrl($data));
        $this->assertEquals($expected, $this->escaper->escapeUrl($expected));
    }

    /**
     * @return array
     */
    public function escapeUrlDataProvider(): array
    {
        return [
            [
                'data' => "http://example.com/search?term=this+%26+that&view=list",
                'expected' => "http://example.com/search?term=this+%26+that&amp;view=list",
            ],
            [
                'data' => "http://exam\r\nple.com/search?term=this+%26+that&view=list",
                'expected' => "http://example.com/search?term=this+%26+that&amp;view=list",
            ],
            [
                'data' => "http://&#x65;&#x78;&#x61;&#x6d;&#x70;&#x6c;&#x65;&#x2e;&#x63;&#x6f;&#x6d;/",
                'expected' => "http://example.com/",
            ],
        ];
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeJsQuote
     */
    public function testEscapeJsQuote()
    {
        $data = ["Don't do that.", 'lost_key' => "Can't do that."];
        $expected = ["Don\\'t do that.", "Can\\'t do that."];
        $this->assertEquals($expected, $this->escaper->escapeJsQuote($data));
        $this->assertEquals($expected[0], $this->escaper->escapeJsQuote($data[0]));
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
        $this->assertEquals($expected[0], $this->escaper->escapeQuote($data));
        $this->assertEquals($expected[1], $this->escaper->escapeQuote($data, true));
    }

    /**
     * @covers \Magento\Framework\Escaper::escapeXssInUrl
     * @param string $input
     * @param string $expected
     * @dataProvider escapeDataProvider
     */
    public function testEscapeXssInUrl($input, $expected)
    {
        $this->assertEquals($expected, $this->escaper->escapeXssInUrl($input));
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
            [
                'http://test.com/?{{{test}}{{test_translated}}{{tes_origin}}{{theme}}}',
                'http://test.com/?test',
            ],
        ];
    }
}
