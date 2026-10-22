<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Integrity\Di;

use Magento\Framework\App\Utility\Files;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DuplicatePreferenceTest extends TestCase
{
    /**
     * @param string $file
     */
    #[DataProvider('diConfigDataProvider')]
    public function testNoDuplicatePreferences(string $file): void
    {
        $document = new \DOMDocument();
        $document->load($file);

        $count = [];
        foreach ($document->getElementsByTagName('preference') as $preference) {
            $for = ltrim($preference->getAttribute('for'), '\\');
            $count[$for] = ($count[$for] ?? 0) + 1;
        }

        $this->assertSame(
            [],
            array_keys(array_filter($count, static fn (int $n): bool => $n > 1)),
            'Duplicate preferences in ' . $file
        );
    }

    /**
     * @return array
     */
    public static function diConfigDataProvider(): array
    {
        return Files::init()->getDiConfigs(true);
    }
}
