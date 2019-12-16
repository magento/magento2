<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Helper;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Cover CSP util use cases.
 */
class InlineUtilTest extends TestCase
{
    /**
     * @var InlineUtil
     */
    private $util;

    /**
     * @var PolicyInterface[]
     */
    private $policiesAdded = [];

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->policiesAdded = [];
        $collectorMock = $this->getMockBuilder(DynamicCollector::class)->disableOriginalConstructor()->getMock();
        $collectorMock->method('add')
            ->willReturnCallback(
                function (PolicyInterface $policy) {
                    $this->policiesAdded[] = $policy;
                }
            );
        $this->util = Bootstrap::getObjectManager()->create(InlineUtil::class, ['dynamicCollector' => $collectorMock]);
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
     * @dataProvider getTags
     */
    public function testRenderTag(
        string $tagName,
        array $attributes,
        ?string $content,
        string $result,
        array $policiesExpected
    ): void {
        $this->assertEquals($result, $this->util->renderTag($tagName, $attributes, $content));
        $this->assertEquals($policiesExpected, $this->policiesAdded);
    }

    /**
     * Test data for tag rendering test.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTags(): array
    {
        return [
            'remote-script' => [
                'script',
                ['src' => 'http://magento.com/static/some-script.js'],
                null,
                '<script src="http://magento.com/static/some-script.js" />',
                [new FetchPolicy('script-src', false, ['http://magento.com'])]
            ],
            'inline-script' => [
                'script',
                ['type' => 'text/javascript'],
                "\n    let someVar = 25;\n    document.getElementById('test').innerText = someVar;\n",
                "<script type=\"text/javascript\">\n    let someVar = 25;"
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
                        [],
                        ['U+SKpEef030N2YgyKKdIBIvPy8Fmd42N/JcTZgQV+DA=' => 'sha256']
                    )
                ]
            ],
            'remote-style' => [
                'link',
                ['rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'http://magento.com/static/style.css'],
                null,
                '<link rel="stylesheet" type="text/css" href="http://magento.com/static/style.css" />',
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
                '<img src="http://magento.com/static/my.jpg" />',
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
                "<style type=\"text/css\">"
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
                "<form action=\"https://magento.com/submit\" method=\"post\">"
                    ."\n    <input type=\"text\" name=\"test\" /><input type=\"submit\" value=\"Submit\" />\n"
                    ."</form>",
                [new FetchPolicy('form-action', false, ['https://magento.com'])]
            ],
            'cross-origin-iframe' => [
                'iframe',
                ['src' => 'http://magento.com/some-page'],
                null,
                '<iframe src="http://magento.com/some-page" />',
                [new FetchPolicy('frame-src', false, ['http://magento.com'])]
            ],
            'remote-track' => [
                'track',
                ['src' => 'http://magento.com/static/track.vtt', 'kind' => 'subtitles'],
                null,
                '<track src="http://magento.com/static/track.vtt" kind="subtitles" />',
                [new FetchPolicy('media-src', false, ['http://magento.com'])]
            ],
            'remote-source' => [
                'source',
                ['src' => 'http://magento.com/static/track.ogg', 'type' => 'audio/ogg'],
                null,
                '<source src="http://magento.com/static/track.ogg" type="audio/ogg" />',
                [new FetchPolicy('media-src', false, ['http://magento.com'])]
            ],
            'remote-video' => [
                'video',
                ['src' => 'https://magento.com/static/video.mp4'],
                null,
                '<video src="https://magento.com/static/video.mp4" />',
                [new FetchPolicy('media-src', false, ['https://magento.com'])]
            ],
            'remote-audio' => [
                'audio',
                ['src' => 'https://magento.com/static/audio.mp3'],
                null,
                '<audio src="https://magento.com/static/audio.mp3" />',
                [new FetchPolicy('media-src', false, ['https://magento.com'])]
            ],
            'remote-object' => [
                'object',
                ['data' => 'http://magento.com/static/flash.swf'],
                null,
                '<object data="http://magento.com/static/flash.swf" />',
                [new FetchPolicy('object-src', false, ['http://magento.com'])]
            ],
            'remote-embed' => [
                'embed',
                ['src' => 'http://magento.com/static/flash.swf'],
                null,
                '<embed src="http://magento.com/static/flash.swf" />',
                [new FetchPolicy('object-src', false, ['http://magento.com'])]
            ],
            'remote-applet' => [
                'applet',
                ['code' => 'SomeApplet.class', 'archive' => 'https://magento.com/applet/my-applet.jar'],
                null,
                '<applet code="SomeApplet.class" archive="https://magento.com/applet/my-applet.jar" />',
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
        $this->assertEquals('onclick="alert()"', $result);
        $this->assertEquals([new FetchPolicy('script-src', false, [], [], false, true)], $this->policiesAdded);
    }
}
