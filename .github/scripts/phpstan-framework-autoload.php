<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

// Registers autoloaders for the CE sparse clone at /tmp/magento2-static/:
//
//   dev/tests/static/framework/  — Magento\PhpStan\* and Magento\CodeMessDetector\*
//   lib/internal/                 — Magento\Framework\* (resolves parent classes and
//                                   parameter types not captured by the use-statement
//                                   stub generator, e.g. FQN in extends clauses)
//
// Loaded via --autoload-file (before PHPStan builds its DI container) only when
// the CE sparse clone is present. When it is absent this file is never referenced.

$bases = [
    '/tmp/magento2-static/dev/tests/static/framework/',
    '/tmp/magento2-static/lib/internal/',
];

$anyPresent = false;
foreach ($bases as $base) {
    if (!is_dir($base)) {
        continue;
    }
    $anyPresent = true;
    spl_autoload_register(static function (string $class) use ($base): void {
        $file = $base . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

if (!$anyPresent) {
    return;
}
