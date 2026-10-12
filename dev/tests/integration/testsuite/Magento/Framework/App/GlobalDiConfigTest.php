<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use PHPUnit\Framework\TestCase;

class GlobalDiConfigTest extends TestCase
{
    public function testGlobalDiHasNoDuplicatePreferences(): void
    {
        $document = new \DOMDocument();
        $document->load(BP . '/app/etc/di.xml');

        $seen = [];
        foreach ($document->getElementsByTagName('preference') as $preference) {
            $for = ltrim($preference->getAttribute('for'), '\\');
            $seen[$for][] = ltrim($preference->getAttribute('type'), '\\');
        }
        $duplicates = array_keys(array_filter($seen, static fn (array $types): bool => count($types) > 1));

        $this->assertSame([], $duplicates, 'Duplicate preferences in app/etc/di.xml');
    }
}
