<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Filesystem;

use Magento\Framework\Filesystem\Glob;
use PHPUnit\Framework\TestCase;

class GlobTest extends TestCase
{
    public function testClearCache(): void
    {
        $dir = __DIR__ . '/_files/glob';
        $pattern = $dir . '/*.txt';
        $testFile = $dir . '/c.txt';
        try {
            if (is_file($testFile)) {
                unlink($testFile);
            }
            $this->assert(['a.txt', 'b.txt'], Glob::glob($pattern));
            touch($testFile);
            $this->assert(['a.txt', 'b.txt'], Glob::glob($pattern));
            Glob::clearCache();
            $this->assert(['a.txt', 'b.txt', 'c.txt'], Glob::glob($pattern));
        } finally {
            if (is_file($testFile)) {
                unlink($testFile);
            }
        }
    }

    private function assert(array $expected, array $results): void
    {
        $results = array_map(static function (string $file): string {
            return substr($file, strrpos($file, '/') + 1);
        }, $results);
        $missing = array_diff($expected, $results);
        $this->assertEmpty($missing, 'Missing files: ' . implode(', ', $missing));
        $unexpected = array_diff($results, $expected);
        $this->assertEmpty($unexpected, 'Unexpected files: ' . implode(', ', $unexpected));
    }
}
