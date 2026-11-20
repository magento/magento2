<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\Symfony\IdentifierNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for IdentifierNormalizer
 */
class IdentifierNormalizerTest extends TestCase
{
    /**
     * @param string $identifier
     * @param string $expected
     */
    #[DataProvider('normalizeDataProvider')]
    public function testNormalize(string $identifier, string $expected): void
    {
        $this->assertSame($expected, IdentifierNormalizer::normalize($identifier));
    }

    /**
     * @return array
     */
    public static function normalizeDataProvider(): array
    {
        return [
            'lowercase is upper-cased' => ['abc', 'ABC'],
            'mixed case is upper-cased' => ['mixedCase', 'MIXEDCASE'],
            'already normalized is unchanged' => ['CACHE_KEY_1', 'CACHE_KEY_1'],
            'dot becomes double underscore' => ['a.b', 'A__B'],
            'dash becomes single underscore' => ['a-b', 'A_B'],
            'space becomes single underscore' => ['a b', 'A_B'],
            'mixed special characters' => ['a.b-c d', 'A__B_C_D'],
            'digits are preserved' => ['key123', 'KEY123'],
            'empty string stays empty' => ['', ''],
        ];
    }

    /**
     * The same identifier must always normalize to the same key (idempotency of normalized input).
     */
    public function testNormalizeIsStableForNormalizedInput(): void
    {
        $once = IdentifierNormalizer::normalize('Some.Cache-Id 1');
        $twice = IdentifierNormalizer::normalize($once);

        $this->assertSame($once, $twice);
    }
}
