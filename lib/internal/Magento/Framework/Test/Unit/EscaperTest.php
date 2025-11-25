<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline;
use Magento\Framework\ZendEscaper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\Translate\Inline\StateInterface;

/**
 * \Magento\Framework\Escaper test case
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EscaperTest extends TestCase
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ZendEscaper
     */
    private $zendEscaper;

    /**
     * @var Inline
     */
    private $translateInline;

    /**
     * @var LoggerInterface
     */
    private $loggerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->escaper = new Escaper();
        $this->zendEscaper = new ZendEscaper();
        $this->translateInline = $this->objectManagerHelper->getObject(Inline::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->objectManagerHelper->setBackwardCompatibleProperty($this->escaper, 'escaper', $this->zendEscaper);
        $this->objectManagerHelper->setBackwardCompatibleProperty($this->escaper, 'logger', $this->loggerMock);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
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
    public static function escapeJsDataProvider()
    {
        return [
            'zero length string' => ['', ''],
            'null as string' => [null, ''],
            'Magento\Framework\Phrase as string' => [__('test'), 'test'],
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
     * Tests escapeHtmlAttr method when Inline translate is configured.
     *
     * @param string $input
     * @param string $output
     * @return void
     * @dataProvider escapeHtmlAttributeWithInlineTranslateEnabledDataProvider
     */
    public function testEscapeHtmlAttributeWithInlineTranslateEnabled(string $input, string $output): void
    {
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->translateInline,
            'isAllowed',
            true
        );
        $stateMock = $this->createMock(StateInterface::class);
        $stateMock->method('isEnabled')
            ->willReturn(true);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->translateInline,
            'state',
            $stateMock
        );

        $actual = $this->escaper->escapeHtmlAttr($input);
        $this->assertEquals($output, $actual);
    }

    /**
     * Data provider for escapeHtmlAttrWithInline test.
     *
     * @return array
     */
    public static function escapeHtmlAttributeWithInlineTranslateEnabledDataProvider(): array
    {
        return [
            [
                '{{{Search entire store here...}}}',
                '{{{Search&#x20;entire&#x20;store&#x20;here...}}}',
            ],
            [
                '{{{Product search}}{{Translated to language}}{{themeMagento/Luma}}}',
                '{{{Product&#x20;search}}{{Translated&#x20;to&#x20;language}}{{themeMagento&#x2F;Luma}}}',
            ],
            [
                'Simple string',
                'Simple&#x20;string',
            ],
        ];
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
    public static function escapeHtmlDataProvider()
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
                'data' => 'Some test <span style="fine">text in span tag</span> <strong>text in strong tag</strong> '
                    . '<a type="some-type" href="http://domain.com/" style="bad" onclick="alert(1)">'
                    . 'Click here</a><script>alert(1)'
                    . '</script>',
                'expected' => 'Some test <span style="fine">text in span tag</span> text in strong tag '
                    . '<a href="http://domain.com/">'
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
            'text with japanese lang' => [
                'data' => '<span>だ だ だ some text in tags<br /></span>',
                'expected' => '<span>だ だ だ some text in tags</span>',
                'allowedTags' => ['span'],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function escapeHtmlInvalidDataProvider()
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
     * @covers \Magento\Framework\Escaper::escapeCss
     *
     * @param string $data
     * @param string $expected
     * @return void
     *
     * @dataProvider escapeCssDataProvider
     */
    public function testEscapeCss($data, string $expected): void
    {
        $this->assertEquals($expected, $this->escaper->escapeCss($data));
    }

    /**
     * @return array
     */
    public static function escapeCssDataProvider(): array
    {
        return [
            [
                'data' => 1,
                'expected' => '1',
            ],
            [
                'data' => '*%string{foo}%::',
                'expected' => '\2A \25 string\7B foo\7D \25 \3A \3A ',
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\Escaper::encodeUrlParam
     *
     * @param string $data
     * @param string $expected
     * @return void
     *
     * @dataProvider encodeUrlParamDataProvider
     */
    public function testEncodeUrlParam($data, string $expected): void
    {
        $this->assertEquals($expected, $this->escaper->encodeUrlParam($data));
    }

    /**
     * @return array
     */
    public static function encodeUrlParamDataProvider(): array
    {
        return [
            [
                'data' => "a3==",
                'expected' => "a3%3D%3D",
            ],
            [
                'data' => "example string",
                'expected' => "example%20string",
            ],
            [
                'data' => 1,
                'expected' => "1",
            ],
            [
                'data' => null,
                'expected' => "",
            ]
        ];
    }

    /**
     * @return array
     */
    public static function escapeUrlDataProvider(): array
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
    public static function escapeDataProvider()
    {
        return [
            [
                '0',
                '0',
            ],
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

    public function testEscapeXssInUrlWithMalformedUtf8ReturnsEmptyString(): void
    {
        // Invalid UTF-8 byte sequence followed by a script identifier
        $bad = "\xC3\x28javascript:alert(1)";
        $this->assertSame('', $this->escaper->escapeXssInUrl($bad));
    }

    public function testInlineSensitiveEscapeHtmlAttrWithTripleBraces(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'inlineSensitiveEscapeHtmlAttr');
        $method->setAccessible(true);

        $input = '{{{Search entire store here...}}}';
        $expected = '{{{Search&#x20;entire&#x20;store&#x20;here...}}}';

        $this->assertSame($expected, $method->invoke($this->escaper, $input));
    }

    public function testInlineSensitiveEscapeHtmlAttrWithoutTripleBracesFallsBack(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'inlineSensitiveEscapeHtmlAttr');
        $method->setAccessible(true);

        $input = 'Simple string';
        $expected = 'Simple&#x20;string';

        $this->assertSame($expected, $method->invoke($this->escaper, $input));
    }

    public function testEscapeScriptIdentifiersReplacesKnownIdentifiers(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'escapeScriptIdentifiers');
        $method->setAccessible(true);

        $input = 'prefix javascript:alert(1) and vbscript:and data:text/plain';
        $expected = 'prefix :alert(1) and :and :text/plain';

        $this->assertSame($expected, $method->invoke($this->escaper, $input));
    }

    public function testEscapeScriptIdentifiersReturnsEmptyOnSecondPregReplaceError(): void
    {
        $patternProp = new \ReflectionProperty(Escaper::class, 'xssFiltrationPattern');
        $patternProp->setAccessible(true);
        $original = $patternProp->getValue();

        // Force preg_replace to fail and return null using an invalid pattern
        $patternProp->setValue('/[/');

        try {
            // preg_replace compilation error emits a PHP warning; swallow it locally for this test
            set_error_handler(function () {
                return true; // suppress warning from invalid regex
            });
            $method = new \ReflectionMethod(Escaper::class, 'escapeScriptIdentifiers');
            $method->setAccessible(true);

            $this->assertSame('', $method->invoke($this->escaper, 'javascript:alert(1)'));
        } finally {
            restore_error_handler();
            // Restore original pattern to avoid side effects on other tests
            $patternProp->setValue($original);
        }
    }

    public function testEscapeScriptIdentifiersRecursiveReprocessing(): void
    {
        $patternProp = new \ReflectionProperty(Escaper::class, 'xssFiltrationPattern');
        $patternProp->setAccessible(true);
        $original = $patternProp->getValue();

        // Use a pattern that still matches after replacement to trigger recursion
        $patternProp->setValue('/::/');

        try {
            $method = new \ReflectionMethod(Escaper::class, 'escapeScriptIdentifiers');
            $method->setAccessible(true);

            $this->assertSame(':', $method->invoke($this->escaper, ':::'));
        } finally {
            $patternProp->setValue($original);
        }
    }

    public function testPrepareUnescapedCharactersReplacesAmpersands(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'prepareUnescapedCharacters');
        $method->setAccessible(true);

        $input = '& < & >';
        $expected = '&amp; < &amp; >';

        $this->assertSame($expected, $method->invoke($this->escaper, $input));
    }

    public function testPrepareUnescapedCharactersNoAmpersandNoOp(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'prepareUnescapedCharacters');
        $method->setAccessible(true);

        $input = 'Plain text without ampersands';

        $this->assertSame($input, $method->invoke($this->escaper, $input));
    }

    public function testPrepareUnescapedCharactersReturnsNullOnMalformedUtf8(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'prepareUnescapedCharacters');
        $method->setAccessible(true);

        // Invalid UTF-8 sequence; with /u modifier preg_replace returns null
        $bad = "\xC3\x28 &";

        set_error_handler(function () {
            return true; // Suppress preg_replace warning for invalid UTF-8 subject
        });
        try {
            $this->assertNull($method->invoke($this->escaper, $bad));
        } finally {
            restore_error_handler();
        }
    }

    public function testRemoveNotAllowedTagsStripsDisallowedAndKeepsAllowed(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'a');
        $div->appendChild($doc->createTextNode('keep '));
        $script = $doc->createElement('script', 'alert(1)');
        $div->appendChild($script);
        $div->appendChild($doc->createTextNode(' '));
        $div2 = $doc->createElement('div', 'div content');
        $div->appendChild($div2);
        $div->appendChild($doc->createTextNode(' '));
        $bold = $doc->createElement('b', 'bold');
        $div->appendChild($bold);
        $body->appendChild($div);

        $method = new \ReflectionMethod(Escaper::class, 'removeNotAllowedTags');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc, ['span', 'b']);

        $xpath = new \DOMXPath($doc);
        $this->assertSame(0, $xpath->query('//script')->length);
        $this->assertSame(0, $xpath->query('//div')->length);
        $this->assertSame(0, $xpath->query('//b')->length);

        $bodyNode = $xpath->query('//body')->item(0);
        $this->assertNotNull($bodyNode);
        $bodyText = $bodyNode->textContent;
        $this->assertStringContainsString('alert(1)', $bodyText);
        $this->assertStringContainsString('div content', $bodyText);
        $this->assertStringContainsString('bold', $bodyText);
    }

    public function testRemoveNotAllowedTagsNoAllowedLeavesOnlyText(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $div = $doc->createElement('div', 'with ');
        $b = $doc->createElement('b', 'nested');
        $div->appendChild($b);
        $div->appendChild($doc->createTextNode(' tags'));
        $body->appendChild($div);

        $method = new \ReflectionMethod(Escaper::class, 'removeNotAllowedTags');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc, []);

        $xpath = new \DOMXPath($doc);
        // Only html and body elements should remain; their children should be text nodes
        $this->assertSame(0, $xpath->query('//*[name() != "html" and name() != "body"]')->length);
        $body = $xpath->query('//body')->item(0);
        $this->assertNotNull($body);
        $this->assertStringContainsString('with nested tags', $body->textContent);
    }

    public function testRemoveNotAllowedAttributesKeepsAllowedOnNonAnchor(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $span = $doc->createElement('span');
        $span->setAttribute('id', 'sid');
        $span->setAttribute('class', 'c');
        $span->setAttribute('style', 'color:red');
        $span->setAttribute('title', 't');
        $span->setAttribute('href', '#');
        $span->setAttribute('data-x', '1');
        $span->setAttribute('onclick', 'evil');
        $body->appendChild($span);

        $method = new \ReflectionMethod(Escaper::class, 'removeNotAllowedAttributes');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc);

        $xpath = new \DOMXPath($doc);
        /** @var \DOMElement $span */
        $span = $xpath->query('//span')->item(0);
        $this->assertNotNull($span);
        // Allowed globally
        $this->assertTrue($span->hasAttribute('id'));
        $this->assertTrue($span->hasAttribute('class'));
        $this->assertTrue($span->hasAttribute('style'));
        $this->assertTrue($span->hasAttribute('title'));
        $this->assertTrue($span->hasAttribute('href'));
        // Disallowed should be stripped
        $this->assertFalse($span->hasAttribute('data-x'));
        $this->assertFalse($span->hasAttribute('onclick'));
    }

    public function testRemoveNotAllowedAttributesRemovesStyleFromAnchor(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $a = $doc->createElement('a');
        $a->setAttribute('id', 'aid');
        $a->setAttribute('href', 'http://example.com');
        $a->setAttribute('style', 'color:red');
        $a->setAttribute('class', 'c');
        $a->setAttribute('onclick', 'evil');
        $body->appendChild($a);

        $method = new \ReflectionMethod(Escaper::class, 'removeNotAllowedAttributes');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc);

        $xpath = new \DOMXPath($doc);
        /** @var \DOMElement $a */
        $a = $xpath->query('//a')->item(0);
        $this->assertNotNull($a);
        // Allowed globally and should remain
        $this->assertTrue($a->hasAttribute('id'));
        $this->assertTrue($a->hasAttribute('href'));
        $this->assertTrue($a->hasAttribute('class'));
        // Disallowed globally
        $this->assertFalse($a->hasAttribute('onclick'));
        // Special-case disallowed for <a>: style must be removed
        $this->assertFalse($a->hasAttribute('style'));
    }

    public function testRemoveCommentsRemovesHtmlCommentsEverywhere(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $div = $doc->createElement('div', 'Before ');
        $comment = $doc->createComment(' single comment ');
        $div->appendChild($comment);
        $div->appendChild($doc->createTextNode('text'));
        $body->appendChild($div);

        $div2 = $doc->createElement('div');
        $div2->appendChild($doc->createComment(' inner comment '));
        $div->appendChild($div2);

        $script = $doc->createElement('script');
        $script->appendChild($doc->createComment(' script inner '));
        $div->appendChild($script);

        $comment2 = $doc->createComment(' tail ');
        $div->appendChild($comment2);
        $body->appendChild($div);

        $method = new \ReflectionMethod(Escaper::class, 'removeComments');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc);

        $xpath = new \DOMXPath($doc);
        $this->assertSame(0, $xpath->query('//comment()')->length);
        $body = $xpath->query('//body')->item(0);
        $this->assertNotNull($body);
        $this->assertStringNotContainsString('comment', $body->textContent);
        $this->assertStringContainsString('Before', $body->textContent);
        $this->assertStringContainsString('text', $body->textContent);
    }

    public function testRemoveCommentsHandlesMultiLineComments(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $div = $doc->createElement('div');
        $div->appendChild($doc->createComment(" multi\nline\ncomment "));
        $p = $doc->createElement('p', 'content');
        $div->appendChild($p);
        $div->appendChild($doc->createComment(" another\n"));
        $body->appendChild($div);

        $method = new \ReflectionMethod(Escaper::class, 'removeComments');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc);

        $xpath = new \DOMXPath($doc);
        $this->assertSame(0, $xpath->query('//comment()')->length);
        $body = $xpath->query('//body')->item(0);
        $this->assertNotNull($body);
        $this->assertStringContainsString('content', $body->textContent);
        $this->assertStringNotContainsString('comment', $body->textContent);
    }

    public function testEscapeTextEscapesSpecialCharsInTextNodes(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'one');
        $div->appendChild($doc->createTextNode('A < B & C > D " E \' F'));
        $body->appendChild($div);

        $span = $doc->createElement('span');
        $span->setAttribute('id', 'two');
        $span->setAttribute('data-x', '1 & 2');
        $span->appendChild($doc->createTextNode('prefix '));
        $bold = $doc->createElement('b', 'bold');
        $span->appendChild($bold);
        $span->appendChild($doc->createTextNode(' suffix & more'));
        $body->appendChild($span);

        $method = new \ReflectionMethod(Escaper::class, 'escapeText');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc);

        $xpath = new \DOMXPath($doc);

        // Verify div text node has escaped characters now literally in text content
        /** @var \DOMNodeList $nodes */
        $nodes = $xpath->query('//*[@id="one"]/text()');
        $this->assertGreaterThan(0, $nodes->length);
        $divText = '';
        foreach ($nodes as $n) {
            $divText .= $n->textContent;
        }
        $this->assertStringContainsString('&lt;', $divText);
        $this->assertStringContainsString('&gt;', $divText);
        $this->assertStringContainsString('&amp;', $divText);
        $this->assertStringContainsString('&quot;', $divText);
        $this->assertStringContainsString('&#039;', $divText);

        // Attributes must remain logically unchanged
        /** @var \DOMNodeList $spanNodes */
        $spanNodes = $xpath->query('//*[@id="two"]');
        /** @var \DOMElement $span */
        $span = $spanNodes->item(0);
        $this->assertNotNull($span);
        $this->assertSame('1 & 2', $span->getAttribute('data-x'));

        // The trailing "& more" text node should be escaped to &amp;
        /** @var \DOMNodeList $tailNodes */
        $tailNodes = $xpath->query('//*[@id="two"]/text()[last()]');
        $this->assertGreaterThan(0, $tailNodes->length);
        $this->assertSame(' suffix &amp; more', $tailNodes->item(0)->textContent);
    }

    public function testEscapeAttributeValuesEscapesHrefAndHtmlAttributes(): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $html = $doc->createElement('html');
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->appendChild($body);

        $a = $doc->createElement('a', 'link');
        $a->setAttribute('id', 'link1');
        $a->setAttribute('href', 'http://test.com/?redirect=javascript:alert(1)&test=1');
        $a->setAttribute('title', "He said \"Hi\" & 'ok' < >");
        $a->setAttribute('data-x', 'alpha & beta');
        $body->appendChild($a);

        $method = new \ReflectionMethod(Escaper::class, 'escapeAttributeValues');
        $method->setAccessible(true);
        $method->invoke($this->escaper, $doc);

        $xpath = new \DOMXPath($doc);
        /** @var \DOMNodeList $linkNodes */
        $linkNodes = $xpath->query('//*[@id="link1"]');
        /** @var \DOMElement $el */
        $el = $linkNodes->item(0);
        $this->assertNotNull($el);

        // href should be sanitized via escapeUrl (xss identifiers removed, ampersand encoded)
        $this->assertSame(
            'http://test.com/?redirect=:alert(1)&amp;test=1',
            $el->getAttribute('href')
        );

        // title should be HTML-escaped
        $title = $el->getAttribute('title');
        $this->assertStringContainsString('&quot;Hi&quot;', $title);
        $this->assertStringContainsString('&#039;ok&#039;', $title);
        $this->assertStringContainsString('&amp;', $title);
        $this->assertStringContainsString('&lt;', $title);
        $this->assertStringContainsString('&gt;', $title);

        // data-x should be HTML-escaped (ampersand only here)
        $this->assertSame('alpha &amp; beta', $el->getAttribute('data-x'));
    }

    public function testEscapeAttributeValueHrefUsesEscapeUrl(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'escapeAttributeValue');
        $method->setAccessible(true);

        $name = 'href';
        $value = 'http://test.com/?redirect=javascript:alert(1)&test=1';

        $escaped = $method->invoke($this->escaper, $name, $value);

        $this->assertSame('http://test.com/?redirect=:alert(1)&amp;test=1', $escaped);
    }

    public function testEscapeAttributeValueNonHrefUsesEscapeHtml(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'escapeAttributeValue');
        $method->setAccessible(true);

        $name = 'title';
        $value = 'He said "Hi" & \'ok\' < >';

        $escaped = $method->invoke($this->escaper, $name, $value);

        $this->assertStringContainsString('&quot;Hi&quot;', $escaped);
        $this->assertStringContainsString('&#039;ok&#039;', $escaped);
        $this->assertStringContainsString('&amp;', $escaped);
        $this->assertStringContainsString('&lt;', $escaped);
        $this->assertStringContainsString('&gt;', $escaped);
    }

    public function testFilterProhibitedTagsRemovesDisallowedAndLogs(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'filterProhibitedTags');
        $method->setAccessible(true);

        // Expect a critical log mentioning the prohibited tag
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->stringContains('script'));

        $input = ['span', 'script', 'a'];
        $result = $method->invoke($this->escaper, $input);

        $this->assertSame(['span', 'a'], array_values($result));
    }

    public function testFilterProhibitedTagsNoLogWhenClean(): void
    {
        $method = new \ReflectionMethod(Escaper::class, 'filterProhibitedTags');
        $method->setAccessible(true);

        // No prohibited tags, so no logging
        $this->loggerMock->expects($this->never())
            ->method('critical');

        $input = ['span', 'a'];
        $result = $method->invoke($this->escaper, $input);

        $this->assertSame($input, $result);
    }

    public function testGetEscaperReturnsExistingInstance(): void
    {
        $existing = new ZendEscaper();
        $prop = new \ReflectionProperty(Escaper::class, 'escaper');
        $prop->setAccessible(true);
        $prop->setValue($this->escaper, $existing);

        $method = new \ReflectionMethod(Escaper::class, 'getEscaper');
        $method->setAccessible(true);
        $result = $method->invoke($this->escaper);

        $this->assertSame($existing, $result);
    }

    public function testGetEscaperFetchesFromObjectManagerWhenNull(): void
    {
        $prop = new \ReflectionProperty(Escaper::class, 'escaper');
        $prop->setAccessible(true);
        $prop->setValue($this->escaper, null);

        $zendEscaper = new ZendEscaper();

        $rp = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $rp->setAccessible(true);
        $originalOm = $rp->getValue();
        $stubOm = new class($zendEscaper) implements \Magento\Framework\ObjectManagerInterface
        {
            /**
             * @var \Magento\Framework\ZendEscaper
             */
            private $instance;

            public function __construct($instance)
            {
                $this->instance = $instance;
            }

            /**
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function get($type)
            {
                $unusedType = $type;
                unset($unusedType);
                return $this->instance;
            }

            /**
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function create($type, array $arguments = [])
            {
                $unusedType = $type;
                $unusedArguments = $arguments;
                unset($unusedType, $unusedArguments);
                return $this->get($type);
            }

            public function configure(array $configuration)
            {
            }
        };
        \Magento\Framework\App\ObjectManager::setInstance($stubOm);

        try {
            $method = new \ReflectionMethod(Escaper::class, 'getEscaper');
            $method->setAccessible(true);
            $result = $method->invoke($this->escaper);
            $this->assertSame($zendEscaper, $result);
        } finally {
            if ($originalOm) {
                \Magento\Framework\App\ObjectManager::setInstance($originalOm);
            } else {
                $rp->setValue(null, null);
            }
        }
    }

    public function testGetTranslateInlineReturnsExistingInstance(): void
    {
        $existing = $this->getMockForAbstractClass(\Magento\Framework\Translate\InlineInterface::class);
        $prop = new \ReflectionProperty(Escaper::class, 'translateInline');
        $prop->setAccessible(true);
        $prop->setValue($this->escaper, $existing);

        $method = new \ReflectionMethod(Escaper::class, 'getTranslateInline');
        $method->setAccessible(true);
        $result = $method->invoke($this->escaper);

        $this->assertSame($existing, $result);
    }

    public function testGetTranslateInlineFetchesFromObjectManagerWhenNull(): void
    {
        $prop = new \ReflectionProperty(Escaper::class, 'translateInline');
        $prop->setAccessible(true);
        $prop->setValue($this->escaper, null);

        $inlineMock = $this->getMockForAbstractClass(\Magento\Framework\Translate\InlineInterface::class);

        $rp = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $rp->setAccessible(true);
        $originalOm = $rp->getValue();
        $stubOm = new class($inlineMock) implements \Magento\Framework\ObjectManagerInterface
        {
            /**
             * @var \Magento\Framework\Translate\InlineInterface
             */
            private $instance;

            public function __construct($instance)
            {
                $this->instance = $instance;
            }

            /**
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function get($type)
            {
                $unusedType = $type;
                unset($unusedType);
                return $this->instance;
            }

            /**
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function create($type, array $arguments = [])
            {
                $unusedType = $type;
                $unusedArguments = $arguments;
                unset($unusedType, $unusedArguments);
                return $this->get($type);
            }

            public function configure(array $configuration)
            {
            }
        };
        \Magento\Framework\App\ObjectManager::setInstance($stubOm);

        try {
            $method = new \ReflectionMethod(Escaper::class, 'getTranslateInline');
            $method->setAccessible(true);
            $result = $method->invoke($this->escaper);
            $this->assertSame($inlineMock, $result);
        } finally {
            if ($originalOm) {
                \Magento\Framework\App\ObjectManager::setInstance($originalOm);
            } else {
                $rp->setValue(null, null);
            }
        }
    }

    public function testGetLoggerFetchesFromObjectManagerWhenNull(): void
    {
        $refProp = new \ReflectionProperty(Escaper::class, 'logger');
        $refProp->setAccessible(true);
        $refProp->setValue($this->escaper, null);

        $loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);

        $rp = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $rp->setAccessible(true);
        $originalOm = $rp->getValue();
        $stubOm = new class($loggerMock) implements \Magento\Framework\ObjectManagerInterface
        {
            /**
             * @var \Psr\Log\LoggerInterface
             */
            private $instance;

            public function __construct($instance)
            {
                $this->instance = $instance;
            }

            /**
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function get($type)
            {
                $unusedType = $type;
                unset($unusedType);
                return $this->instance;
            }

            /**
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function create($type, array $arguments = [])
            {
                $unusedType = $type;
                $unusedArguments = $arguments;
                unset($unusedType, $unusedArguments);
                return $this->get($type);
            }

            public function configure(array $configuration)
            {
            }
        };
        \Magento\Framework\App\ObjectManager::setInstance($stubOm);

        try {
            $refMethod = new \ReflectionMethod(Escaper::class, 'getLogger');
            $refMethod->setAccessible(true);
            $logger = $refMethod->invoke($this->escaper);
            $this->assertSame($loggerMock, $logger);
        } finally {
            if ($originalOm) {
                \Magento\Framework\App\ObjectManager::setInstance($originalOm);
            } else {
                $rp->setValue(null, null);
            }
        }
    }

    public function testGetLoggerReturnsExistingInstance(): void
    {
        $refProp = new \ReflectionProperty(Escaper::class, 'logger');
        $refProp->setAccessible(true);
        $refProp->setValue($this->escaper, $this->loggerMock);

        $refMethod = new \ReflectionMethod(Escaper::class, 'getLogger');
        $refMethod->setAccessible(true);
        $logger = $refMethod->invoke($this->escaper);

        $this->assertSame($this->loggerMock, $logger);
    }
}
