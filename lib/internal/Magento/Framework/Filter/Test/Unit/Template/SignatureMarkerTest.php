<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\Template;

use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\Template\SignatureMarker;
use Magento\Framework\Filter\Template\SignatureProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SignatureMarkerTest extends TestCase
{
    private const string SIGNATURE = 'Z0FFbeCU2R8bsVGJuTdkXyiiZBzsaceV';

    /**
     * @var SignatureMarker
     */
    private $signatureMarker;

    /**
     * @var SignatureProvider|MockObject
     */
    private $signatureProvider;

    protected function setUp(): void
    {
        $this->signatureProvider = $this->createPartialMock(SignatureProvider::class, ['get']);

        $this->signatureProvider->method('get')
            ->willReturn(self::SIGNATURE);

        $this->signatureMarker = new SignatureMarker($this->signatureProvider);
    }

    /**
     * Builds the value a wrapped directive is expected to take.
     *
     * @param string $content
     * @return string
     */
    private function marked(string $content): string
    {
        return self::SIGNATURE . ':deferred:' . $content . ':end:' . self::SIGNATURE;
    }

    public function testWrapSurroundsContentWithMarkerPair(): void
    {
        $this->assertSame(
            $this->marked('{{inlinecss file="css/email-inline.css"}}'),
            $this->signatureMarker->wrap('{{inlinecss file="css/email-inline.css"}}')
        );
    }

    public function testContainsSignatureDetectsSignatureAnywhere(): void
    {
        $this->assertTrue($this->signatureMarker->containsSignature($this->marked('{{inlinecss}}')));
        $this->assertTrue($this->signatureMarker->containsSignature('lorem ' . self::SIGNATURE . ' ipsum'));
        $this->assertFalse($this->signatureMarker->containsSignature('{{inlinecss}}'));
    }

    public function testHasSingleMarkerPairAcceptsWrappedConstruction(): void
    {
        $this->assertTrue($this->signatureMarker->hasSingleMarkerPair($this->marked('{{inlinecss}}')));
    }

    /**
     * @param string $construction
     */
    #[DataProvider('unsoundConstructionDataProvider')]
    public function testHasSingleMarkerPairRejectsUnsoundConstruction(string $construction): void
    {
        $this->assertFalse($this->signatureMarker->hasSingleMarkerPair($construction));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unsoundConstructionDataProvider(): array
    {
        $opening = self::SIGNATURE . ':deferred:';
        $closing = ':end:' . self::SIGNATURE;

        return [
            'no markers at all' => ['{{inlinecss}}'],
            'opening marker only' => [$opening . '{{inlinecss}}'],
            'closing marker only' => ['{{inlinecss}}' . $closing],
            'leading content before opening marker' => ['x' . $opening . '{{inlinecss}}' . $closing],
            'trailing content after closing marker' => [$opening . '{{inlinecss}}' . $closing . 'x'],
            'nested marker pair in body' => [
                $opening . $opening . '{{inlinecss}}' . $closing . $closing
            ],
            'bare signature smuggled into body' => [
                $opening . '{{inlinecss file="' . self::SIGNATURE . '"}}' . $closing
            ],
        ];
    }

    public function testStripMarkersRemovesEveryMarker(): void
    {
        $value = 'a' . $this->marked('{{one}}') . 'b' . $this->marked('{{two}}') . 'c';

        $this->assertSame('a{{one}}b{{two}}c', $this->signatureMarker->stripMarkers($value));
    }

    public function testStripMarkersLeavesUnmarkedValueUntouched(): void
    {
        $this->assertSame('{{inlinecss}}', $this->signatureMarker->stripMarkers('{{inlinecss}}'));
    }

    public function testEmbedIntoPatternMatchesOnlyMarkedConstruction(): void
    {
        $pattern = $this->signatureMarker->embedIntoPattern(Template::CONSTRUCTION_TEMPLATE_PATTERN);

        $this->assertSame(1, preg_match($pattern, $this->marked('{{template config_path="a/b/c"}}')));
        $this->assertSame(0, preg_match($pattern, '{{template config_path="a/b/c"}}'));
    }

    public function testEmbedIntoPatternHandlesBracketStyleDelimiters(): void
    {
        $pattern = $this->signatureMarker->embedIntoPattern('({{(template)(.*?)}})si');

        $this->assertSame(1, preg_match($pattern, $this->marked('{{template config_path="a/b/c"}}')));
        $this->assertSame(0, preg_match($pattern, '{{template config_path="a/b/c"}}'));
    }

    public function testEmbedIntoPatternKeepsPatternModifiers(): void
    {
        $pattern = $this->signatureMarker->embedIntoPattern(Template::CONSTRUCTION_TEMPLATE_PATTERN);

        $this->assertStringEndsWith('/si', $pattern);
    }
}
