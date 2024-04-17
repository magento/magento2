<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Helper;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests \Magento\Sales\Helper\Admin
 */
class AdminTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Admin
     */
    private $helper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helper = Bootstrap::getObjectManager()->create(Admin::class);
    }

    /**
     * @param string $data
     * @param string $expected
     * @param null|array $allowedTags
     * @return void
     *
     * @dataProvider escapeHtmlWithLinksDataProvider
     */
    public function testEscapeHtmlWithLinks(string $data, string $expected, $allowedTags = null): void
    {
        $actual = $this->helper->escapeHtmlWithLinks($data, $allowedTags);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function escapeHtmlWithLinksDataProvider(): array
    {
        return [
            [
                '<a>some text in tags</a>',
                '&lt;a&gt;some text in tags&lt;/a&gt;',
                'allowedTags' => null,
            ],
            [
                // @codingStandardsIgnoreStart
                'Authorized amount of €30.00. Transaction ID: "<a target="_blank" href="https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=123456789QWERTY">123456789QWERTY</a>"',
                'Authorized amount of €30.00. Transaction ID: &quot;<a href="https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&amp;id=123456789QWERTY">123456789QWERTY</a>&quot;',
                // @codingStandardsIgnoreEnd
                'allowedTags' => ['b', 'br', 'strong', 'i', 'u', 'a'],
            ],
            [
                'Transaction ID: "<a target="_blank" href="https://www.paypal.com/?id=XX123XX">XX123XX</a>"',
                'Transaction ID: &quot;<a href="https://www.paypal.com/?id=XX123XX">XX123XX</a>&quot;',
                'allowedTags' => ['b', 'br', 'strong', 'i', 'u', 'a'],
            ],
            [
                '<a>some text in tags</a>',
                '<a>some text in tags</a>',
                'allowedTags' => ['a'],
            ],
            [
                "<a><script>alert(1)</script></a>",
                '<a>alert(1)</a>',
                'allowedTags' => ['a'],
            ],
            [
                '<a href=\"#\">Foo</a>',
                '<a href="%5C&quot;#%5C&quot;">Foo</a>',
                'allowedTags' => ['a'],
            ],
            [
                "<a href=http://example.com?foo=1&bar=2&baz[name]=BAZ>Foo</a>",
                '<a href="http://example.com?foo=1&amp;bar=2&amp;baz%5Bname%5D=BAZ">Foo</a>',
                'allowedTags' => ['a'],
            ],
            [
                "<a href=\"javascript&colon;alert(59)\">Foo</a>",
                '<a href="javascript&amp;colon;alert(59)">Foo</a>',
                'allowedTags' => ['a'],
            ],
            [
                "<a href=\"http://example1.com\" href=\"http://example2.com\">Foo</a>",
                '<a href="http://example1.com">Foo</a>',
                'allowedTags' => ['a'],
            ],
            [
                "<a href=\"http://example.com?foo=text with space\">Foo</a>",
                '<a href="http://example.com?foo=text%20with%20space">Foo</a>',
                'allowedTags' => ['a'],
            ],
        ];
    }
}
