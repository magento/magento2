<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Helper;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Collector\DynamicCollectorMock;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Cover CSP util use cases.
 *
 * @magentoAppArea frontend
 */
class InlineUtilTest extends TestCase
{
    /**
     * @var InlineUtil
     */
    private $util;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureHtmlRenderer;

    /**
     * @var DynamicCollectorMock
     */
    private $dynamicCollector;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                DynamicCollector::class => DynamicCollectorMock::class,
                CspNonceProvider::class => CspNonceProviderMock::class
            ]
        ]);
        $this->util = Bootstrap::getObjectManager()->get(InlineUtil::class);
        $this->secureHtmlRenderer = Bootstrap::getObjectManager()->get(SecureHtmlRenderer::class);
        $this->dynamicCollector = Bootstrap::getObjectManager()->get(DynamicCollector::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->util = null;
        $this->secureHtmlRenderer = null;
        $this->dynamicCollector->consumeAdded();
        $this->dynamicCollector = null;
    }

    /**
     * Test tag rendering.
     *
     * @param string $tagName
     * @param array $attributes
     * @param string|null $content
     * @param string $result Expected result.
     * @param PolicyInterface[] $policiesExpected
     * @return void
     *
     * @dataProvider getTags
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/policy_id script-src
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/eval 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/event_handlers 1
     * @magentoConfigFixture default_store csp/policies/storefront/styles/policy_id style-src
     * @magentoConfigFixture default_store csp/policies/storefront/styles/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/styles/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/styles/inline 0
     */
    public function testRenderTag(
        string $tagName,
        array $attributes,
        ?string $content,
        string $result,
        array $policiesExpected
    ): void {
        $this->assertEquals($result, $this->util->renderTag($tagName, $attributes, $content));
        $this->assertEquals($policiesExpected, $this->dynamicCollector->consumeAdded());
    }

    /**
     * Test data for tag rendering test.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getTags(): array
    {
        return [
            'remote-script' => [
                'script',
                ['src' => 'http://magento.com/static/some-script.js'],
                null,
                '<script src="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;some-script.js"></script>',
                [new FetchPolicy('script-src', false, ['http://magento.com'])]
            ],
            'inline-script' => [
                'script',
                ['type' => 'text/javascript'],
                "\n    let someVar = 25;\n    document.getElementById('test').innerText = someVar;\n",
                "<script type=\"text&#x2F;javascript\" nonce=\"nonce-1234567890abcdef\">\n    let someVar = 25;"
                    ."\n    document.getElementById('test').innerText = someVar;\n</script>",
                [
                    new FetchPolicy(
                        'script-src',
                        false,
                        [],
                        [],
                        false,
                        false,
                        false,
                        ['nonce-1234567890abcdef'],
                        []
                    )
                ]
            ],
            'remote-style' => [
                'link',
                ['rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'http://magento.com/static/style.css'],
                null,
                '<link rel="stylesheet" type="text&#x2F;css"'
                    . ' href="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;style.css"/>',
                [new FetchPolicy('style-src', false, ['http://magento.com'])]
            ],
            'inline-style' => [
                'style',
                [],
                "\n    h1 {color: red;}\n    p {color: green;}\n",
                "<style>\n    h1 {color: red;}\n    p {color: green;}\n</style>",
                [
                    new FetchPolicy(
                        'style-src',
                        false,
                        [],
                        [],
                        false,
                        false,
                        false,
                        [],
                        ['KISO7smrk+XdGrEsiPvVjX6qx4wNef/UKjNb26RaKGM=' => 'sha256']
                    )
                ]
            ],
            'remote-image' => [
                'img',
                ['src' => 'http://magento.com/static/my.jpg'],
                null,
                '<img src="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;my.jpg"/>',
                [new FetchPolicy('img-src', false, ['http://magento.com'])]
            ],
            'remote-font' => [
                'style',
                ['type' => 'text/css'],
                "\n    @font-face {\n        font-family: \"MyCustomFont\";"
                    ."\n        src: url(\"http://magento.com/static/font.ttf\");\n    }\n"
                    ."    @font-face {\n        font-family: \"MyCustomFont2\";"
                    ."\n        src: url('https://magento.com/static/font-2.ttf'),"
                    ."\n             url(static/font.ttf),"
                    ."\n             url(https://devdocs.magento.com/static/another-font.woff),"
                    ."\n             url(http://devdocs.magento.com/static/font.woff);\n    }\n",
                "<style type=\"text&#x2F;css\">"
                    ."\n    @font-face {\n        font-family: \"MyCustomFont\";"
                    ."\n        src: url(\"http://magento.com/static/font.ttf\");\n    }\n"
                    ."    @font-face {\n        font-family: \"MyCustomFont2\";"
                    ."\n        src: url('https://magento.com/static/font-2.ttf'),"
                    ."\n             url(static/font.ttf),"
                    ."\n             url(https://devdocs.magento.com/static/another-font.woff),"
                    ."\n             url(http://devdocs.magento.com/static/font.woff);\n    }\n"
                    ."</style>",
                [
                    new FetchPolicy(
                        'style-src',
                        false,
                        [
                            'http://magento.com',
                            'https://magento.com',
                            'https://devdocs.magento.com',
                            'http://devdocs.magento.com'
                        ]
                    ),
                    new FetchPolicy(
                        'style-src',
                        false,
                        [],
                        [],
                        false,
                        false,
                        false,
                        [],
                        ['TP6Ulnz1kstJ8PYUKvowgJm0phHhtqJnJCnWxKLXkf0=' => 'sha256']
                    )
                ]
            ],
            'cross-origin-form' => [
                'form',
                ['action' => 'https://magento.com/submit', 'method' => 'post'],
                "\n    <input type=\"text\" name=\"test\" /><input type=\"submit\" value=\"Submit\" />\n",
                "<form action=\"https&#x3A;&#x2F;&#x2F;magento.com&#x2F;submit\" method=\"post\">"
                    ."\n    <input type=\"text\" name=\"test\" /><input type=\"submit\" value=\"Submit\" />\n"
                    ."</form>",
                [new FetchPolicy('form-action', false, ['https://magento.com'])]
            ],
            'cross-origin-iframe' => [
                'iframe',
                ['src' => 'http://magento.com/some-page'],
                null,
                '<iframe src="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;some-page"></iframe>',
                [new FetchPolicy('frame-src', false, ['http://magento.com'])]
            ],
            'remote-track' => [
                'track',
                ['src' => 'http://magento.com/static/track.vtt', 'kind' => 'subtitles'],
                null,
                '<track src="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;track.vtt" kind="subtitles"/>',
                [new FetchPolicy('media-src', false, ['http://magento.com'])]
            ],
            'remote-source' => [
                'source',
                ['src' => 'http://magento.com/static/track.ogg', 'type' => 'audio/ogg'],
                null,
                '<source src="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;track.ogg" type="audio&#x2F;ogg"/>',
                [new FetchPolicy('media-src', false, ['http://magento.com'])]
            ],
            'remote-video' => [
                'video',
                ['src' => 'https://magento.com/static/video.mp4'],
                null,
                '<video src="https&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;video.mp4"></video>',
                [new FetchPolicy('media-src', false, ['https://magento.com'])]
            ],
            'remote-audio' => [
                'audio',
                ['src' => 'https://magento.com/static/audio.mp3'],
                null,
                '<audio src="https&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;audio.mp3"></audio>',
                [new FetchPolicy('media-src', false, ['https://magento.com'])]
            ],
            'remote-object' => [
                'object',
                ['data' => 'http://magento.com/static/flash.swf'],
                null,
                '<object data="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;flash.swf"></object>',
                [new FetchPolicy('object-src', false, ['http://magento.com'])]
            ],
            'remote-embed' => [
                'embed',
                ['src' => 'http://magento.com/static/flash.swf'],
                null,
                '<embed src="http&#x3A;&#x2F;&#x2F;magento.com&#x2F;static&#x2F;flash.swf"/>',
                [new FetchPolicy('object-src', false, ['http://magento.com'])]
            ],
            'remote-applet' => [
                'applet',
                ['code' => 'SomeApplet.class', 'archive' => 'https://magento.com/applet/my-applet.jar'],
                null,
                '<applet code="SomeApplet.class" '
                    . 'archive="https&#x3A;&#x2F;&#x2F;magento.com&#x2F;applet&#x2F;my-applet.jar"></applet>',
                [new FetchPolicy('object-src', false, ['https://magento.com'])]
            ]
        ];
    }

    /**
     * Test that inline event listeners are rendered properly.
     *
     * @return void
     */
    public function testRenderEventListener(): void
    {
        $result = $this->util->renderEventListener('onclick', 'alert()');
        $this->assertEquals('onclick="alert&#x28;&#x29;"', $result);
        $this->assertEquals(
            [new FetchPolicy('script-src', false, [], [], false, true)],
            $this->dynamicCollector->consumeAdded()
        );
    }

    /**
     * Check that CSP logic was added to SecureHtmlRenderer
     *
     * @return void
     */
    public function testSecureHtmlRenderer(): void
    {
        $scriptTag = $this->secureHtmlRenderer->renderTag(
            'script',
            ['src' => 'https://test.magento.com/static/script.js']
        );
        $eventListener = $this->secureHtmlRenderer->renderEventListener('onclick', 'alert()');

        $this->assertEquals(
            '<script src="https&#x3A;&#x2F;&#x2F;test.magento.com&#x2F;static&#x2F;script.js"></script>',
            $scriptTag
        );
        $this->assertEquals(
            'onclick="alert&#x28;&#x29;"',
            $eventListener
        );
        $policies = $this->dynamicCollector->consumeAdded();
        $this->assertTrue(in_array(new FetchPolicy('script-src', false, ['https://test.magento.com']), $policies));
        $this->assertTrue(in_array(new FetchPolicy('script-src', false, [], [], false, true), $policies));
    }

    /**
     * Verify that hashes are not calculated when inline sources are allowed.
     *
     * @return void
     *
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/policy_id script-src
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/inline 1
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/eval 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/event_handlers 1
     * @magentoConfigFixture default_store csp/policies/storefront/styles/policy_id style-src
     * @magentoConfigFixture default_store csp/policies/storefront/styles/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/styles/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/styles/inline 1
     */
    public function testRenderWithInline(): void
    {
        $this->assertEquals(
            '<script>alert(1);</script>',
            $this->util->renderTag('script', [], 'alert(1);')
        );
        $this->assertEmpty($this->dynamicCollector->consumeAdded());
    }
}
