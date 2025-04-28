<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\Url\Encoder;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    /**
     * @var Encoder|null
     */
    private ?Encoder $encoder = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->encoder = new Encoder();
    }

    public function testEncode(): void
    {
        $url = 'http://magento2.adobe/encoding';

        self::assertEquals('aHR0cDovL21hZ2VudG8yLmFkb2JlL2VuY29kaW5n', $this->encoder->encode($url));
    }

    /**
     * Equals should be replaced by a non-reserved character.
     */
    public function testEncodeWithEndingSlash(): void
    {
        $url = 'http://magento2.adobe/encoding/with/longer/url/';

        self::assertEquals(
            'aHR0cDovL21hZ2VudG8yLmFkb2JlL2VuY29kaW5nL3dpdGgvbG9uZ2VyL3VybC8~',
            $this->encoder->encode($url)
        );
    }

    /**
     * @dataProvider rfc3986Urls
     *
     * @see          https://www.rfc-editor.org/rfc/rfc3986.html#section-2.2
     */
    public function testEncodeNotContainingRfc3986ReservedCharacters(string $url): void
    {
        $genDelims = [':', '/', '?', '#', '[', ']', '@'];
        $subDelims = ['!', '$', '&', '\'', '(', ')', '*', '+', ',', ';', '='];

        $encodedUrl = $this->encoder->encode($url);

        array_map(static function (string $value) use ($encodedUrl): void {
            self::assertStringNotContainsString($value, $encodedUrl);
        }, $genDelims);

        array_map(static function (string $value) use ($encodedUrl): void {
            self::assertStringNotContainsString($value, $encodedUrl);
        }, $subDelims);
    }

    public static function rfc3986Urls(): array
    {
        return [
            ['http://magento2.adobe/encoding/with/longer/url/'],
            ['http://magento2.adobe/some/other/random/url?currency=eur&price=2'],
            ['http://magento2.adobe/yet/not/done/url#anchor']
        ];
    }
}
